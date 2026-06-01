<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JenisRefill;
use App\Models\Peralatan;
use App\Models\ServicePaket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ServicePaketController extends Controller
{
    public function index()
    {
        $servicePakets = ServicePaket::with(['jenisRefill', 'peralatans'])
            ->withCount('services')
            ->latest()
            ->get();

        return view('admin.service-paket.index', compact('servicePakets'));
    }

    public function create()
    {
        return view('admin.service-paket.create', [
            'servicePaket' => new ServicePaket(),
            'jenisRefills' => JenisRefill::orderBy('nama')->get(),
            'peralatans' => Peralatan::orderBy('nama')->get(),
            'selectedPeralatan' => [],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateRequest($request);

        DB::transaction(function () use ($validated) {
            $servicePaket = ServicePaket::create($validated['service_paket']);
            $servicePaket->peralatans()->sync($validated['peralatan_sync']);
        });

        return redirect()
            ->route('admin.service-paket.index')
            ->with('success', 'Paket service berhasil ditambahkan.');
    }

    public function edit(ServicePaket $servicePaket)
    {
        $servicePaket->load('peralatans');

        return view('admin.service-paket.edit', [
            'servicePaket' => $servicePaket,
            'jenisRefills' => JenisRefill::orderBy('nama')->get(),
            'peralatans' => Peralatan::orderBy('nama')->get(),
            'selectedPeralatan' => $servicePaket->peralatans
                ->mapWithKeys(fn (Peralatan $peralatan) => [
                    $peralatan->id => (int) ($peralatan->pivot->jumlah_estimasi ?? 0),
                ])
                ->all(),
        ]);
    }

    public function update(Request $request, ServicePaket $servicePaket)
    {
        $validated = $this->validateRequest($request, $servicePaket);

        DB::transaction(function () use ($servicePaket, $validated) {
            $servicePaket->update($validated['service_paket']);
            $servicePaket->peralatans()->sync($validated['peralatan_sync']);
        });

        return redirect()
            ->route('admin.service-paket.index')
            ->with('success', 'Paket service berhasil diperbarui.');
    }

    public function destroy(ServicePaket $servicePaket)
    {
        if ($servicePaket->services()->exists()) {
            return back()->with('error', 'Paket service sudah dipakai transaksi dan tidak bisa dihapus.');
        }

        $servicePaket->peralatans()->detach();
        $servicePaket->delete();

        return redirect()
            ->route('admin.service-paket.index')
            ->with('success', 'Paket service berhasil dihapus.');
    }

    private function validateRequest(Request $request, ?ServicePaket $servicePaket = null): array
    {
        $validated = $request->validate([
            'nama' => [
                'required',
                'string',
                'max:255',
                Rule::unique('service_pakets', 'nama')->ignore($servicePaket?->id),
            ],
            'label' => 'nullable|string|max:255',
            'harga' => 'required|numeric|min:0',
            'jenis_refill_id' => 'nullable|exists:jenis_refills,id',
            'refill_ratio' => 'nullable|numeric|min:0',
            'rincian_layanan' => 'nullable|string',
            'peralatan_ids' => 'nullable|array',
            'peralatan_ids.*' => 'exists:peralatans,id',
            'jumlah_estimasi' => 'nullable|array',
        ]);

        $selectedIds = collect($validated['peralatan_ids'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $jumlahEstimasi = collect($request->input('jumlah_estimasi', []));
        $peralatanSync = $selectedIds
            ->mapWithKeys(function (int $peralatanId) use ($jumlahEstimasi) {
                $jumlah = max(1, (int) ($jumlahEstimasi->get((string) $peralatanId) ?? $jumlahEstimasi->get($peralatanId) ?? 1));

                return [$peralatanId => ['jumlah_estimasi' => $jumlah]];
            })
            ->all();

        return [
            'service_paket' => [
                'nama' => $validated['nama'],
                'label' => $validated['label'] ?: $validated['nama'],
                'harga' => (float) $validated['harga'],
                'jenis_refill_id' => $validated['jenis_refill_id'] ?: null,
                'refill_ratio' => filled($validated['refill_ratio'] ?? null) ? (float) $validated['refill_ratio'] : null,
                'rincian_layanan' => $validated['rincian_layanan'] ?: null,
            ],
            'peralatan_sync' => $peralatanSync,
        ];
    }
}
