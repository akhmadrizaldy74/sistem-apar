<?php

namespace App\Services;

use App\Models\Peralatan;
use App\Models\ServicePaket;
use App\Support\ServiceMasterCatalog;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ServiceMasterSyncService
{
    public function sync(): void
    {
        DB::transaction(function () {
            $peralatanMap = $this->syncPeralatan();
            $this->syncServicePakets($peralatanMap);
        });
    }

    public function visiblePeralatans(): EloquentCollection
    {
        $this->sync();

        $orderMap = ServiceMasterCatalog::peralatanOrderMap();

        return Peralatan::query()
            ->whereIn('nama', ServiceMasterCatalog::canonicalPeralatanNames())
            ->get()
            ->sortBy(fn (Peralatan $peralatan) => $orderMap[$peralatan->nama] ?? PHP_INT_MAX)
            ->values();
    }

    public function visibleServicePakets(array $relations = []): EloquentCollection
    {
        $this->sync();

        $orderMap = ServiceMasterCatalog::servicePackageOrderMap();

        return ServicePaket::query()
            ->with($relations)
            ->whereIn('nama', ServiceMasterCatalog::canonicalServicePackageNames())
            ->get()
            ->sortBy(fn (ServicePaket $paket) => $orderMap[$paket->nama] ?? PHP_INT_MAX)
            ->values();
    }

    /**
     * @return array<string, \App\Models\Peralatan>
     */
    private function syncPeralatan(): array
    {
        $allPeralatan = Peralatan::query()
            ->with('servicePakets')
            ->orderBy('id')
            ->get();

        $resolved = [];

        foreach (ServiceMasterCatalog::peralatanDefinitions() as $definition) {
            $canonicalName = (string) $definition['name'];
            $aliases = (array) ($definition['aliases'] ?? []);

            $matches = $allPeralatan->filter(function (Peralatan $peralatan) use ($canonicalName, $aliases) {
                return ServiceMasterCatalog::matchesNameOrAlias($peralatan->nama, $canonicalName, $aliases);
            })->values();

            $survivor = $matches->first(
                fn (Peralatan $peralatan) => ServiceMasterCatalog::normalize($peralatan->nama) === ServiceMasterCatalog::normalize($canonicalName)
            );

            if (! $survivor) {
                $survivor = $matches->first();
            }

            if (! $survivor) {
                $survivor = Peralatan::create([
                    'nama' => $canonicalName,
                    'stok' => 0,
                    'stok_minimum' => (int) ($definition['stok_minimum'] ?? 3),
                    'harga_standar' => (float) ($definition['harga_standar'] ?? 0),
                ]);

                $allPeralatan->push($survivor);
            } else {
                $survivor->forceFill([
                    'nama' => $canonicalName,
                    'stok_minimum' => max((int) ($survivor->stok_minimum ?? 0), (int) ($definition['stok_minimum'] ?? 3)),
                    'harga_standar' => (float) ($definition['harga_standar'] ?? 0),
                ])->save();
            }

            $duplicates = $matches
                ->filter(fn (Peralatan $peralatan) => $peralatan->id !== $survivor->id)
                ->values();

            if ($duplicates->isNotEmpty()) {
                $additionalStock = (int) $duplicates->sum(fn (Peralatan $peralatan) => (int) ($peralatan->stok ?? 0));

                if ($additionalStock > 0) {
                    $survivor->increment('stok', $additionalStock);
                }

                foreach ($duplicates as $duplicate) {
                    $this->moveServicePackageRelations($duplicate, $survivor);

                    if ((int) $duplicate->stok > 0) {
                        $duplicate->forceFill(['stok' => 0])->save();
                    }

                    if (ServiceMasterCatalog::isCanonicalPeralatanName($duplicate->nama)) {
                        $duplicate->forceFill([
                            'nama' => 'Arsip ' . $duplicate->nama . ' #' . $duplicate->id,
                        ])->save();
                    }
                }
            }

            $resolved[$canonicalName] = $survivor->fresh();
        }

        return $resolved;
    }

    /**
     * @param  array<string, \App\Models\Peralatan>  $peralatanMap
     */
    private function syncServicePakets(array $peralatanMap): void
    {
        $allPakets = ServicePaket::query()
            ->with('peralatans')
            ->orderBy('id')
            ->get();

        foreach (ServiceMasterCatalog::servicePackageDefinitions() as $definition) {
            $canonicalName = (string) $definition['name'];
            $aliases = (array) ($definition['aliases'] ?? []);

            $matches = $allPakets->filter(function (ServicePaket $paket) use ($canonicalName, $aliases) {
                return ServiceMasterCatalog::matchesNameOrAlias($paket->nama, $canonicalName, $aliases)
                    || ServiceMasterCatalog::matchesNameOrAlias($paket->label, $canonicalName, $aliases);
            })->values();

            $survivor = $matches->first(
                fn (ServicePaket $paket) => ServiceMasterCatalog::normalize($paket->nama) === ServiceMasterCatalog::normalize($canonicalName)
            );

            if (! $survivor) {
                $survivor = $matches->first();
            }

            $payload = [
                'nama' => $canonicalName,
                'label' => (string) ($definition['label'] ?? $canonicalName),
                'harga' => (float) ($definition['harga'] ?? 0),
                'jenis_refill_id' => null,
                'refill_ratio' => 0,
                'rincian_layanan' => implode(PHP_EOL, (array) ($definition['rincian'] ?? [])),
            ];

            if (! $survivor) {
                $survivor = ServicePaket::create($payload);
                $allPakets->push($survivor);
            } else {
                $survivor->update($payload);
            }

            $syncPayload = collect((array) ($definition['peralatan'] ?? []))
                ->mapWithKeys(function (int $qty, string $peralatanName) use ($peralatanMap) {
                    $peralatan = $peralatanMap[$peralatanName] ?? null;

                    if (! $peralatan) {
                        return [];
                    }

                    return [
                        $peralatan->id => ['jumlah_estimasi' => max(1, $qty)],
                    ];
                })
                ->all();

            $survivor->peralatans()->sync($syncPayload);

            $duplicates = $matches
                ->filter(fn (ServicePaket $paket) => $paket->id !== $survivor->id)
                ->values();

            foreach ($duplicates as $duplicate) {
                if (ServiceMasterCatalog::isCanonicalServicePackageName($duplicate->nama)) {
                    $duplicate->update([
                        'nama' => 'Arsip ' . $duplicate->nama . ' #' . $duplicate->id,
                        'label' => 'Arsip',
                    ]);
                }
            }
        }
    }

    private function moveServicePackageRelations(Peralatan $from, Peralatan $to): void
    {
        if ($from->id === $to->id) {
            return;
        }

        $pivotRows = DB::table('service_paket_peralatan')
            ->where('peralatan_id', $from->id)
            ->get();

        foreach ($pivotRows as $row) {
            $existingQty = (int) (DB::table('service_paket_peralatan')
                ->where('service_paket_id', $row->service_paket_id)
                ->where('peralatan_id', $to->id)
                ->value('jumlah_estimasi') ?? 0);

            $newQty = max(1, $existingQty + (int) ($row->jumlah_estimasi ?? 1));

            DB::table('service_paket_peralatan')->updateOrInsert(
                [
                    'service_paket_id' => $row->service_paket_id,
                    'peralatan_id' => $to->id,
                ],
                [
                    'jumlah_estimasi' => $newQty,
                    'updated_at' => now(),
                    'created_at' => $row->created_at ?? now(),
                ]
            );
        }

        DB::table('service_paket_peralatan')
            ->where('peralatan_id', $from->id)
            ->delete();
    }
}
