<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Jasa;
use App\Models\ServicePaket;
use App\Services\ServiceMasterSyncService;
use Illuminate\Http\Request;

class JasaController extends Controller
{
    /**
     * Tampilkan daftar terpadu Manajemen Jasa & Service.
     */
    public function index(Request $request, ServiceMasterSyncService $serviceMasterSyncService)
    {
        // Sync catalog defaults into ServicePaket table if needed
        $serviceMasterSyncService->sync();

        $search = trim((string) $request->input('search'));

        $query = ServicePaket::with(['jenisRefill', 'peralatans'])->withCount('services');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('label', 'like', "%{$search}%")
                  ->orWhere('rincian_layanan', 'like', "%{$search}%");
            });
        }

        $servicePakets = $query->orderBy('id')->get();

        return view('admin.jasa.index', compact('servicePakets', 'search'));
    }

    /**
     * Redirect create ke form terpadu Tambah Jasa / Service.
     */
    public function create()
    {
        return redirect()->route('admin.service-paket.create');
    }

    /**
     * Simpan data via controller service-paket.
     */
    public function store(Request $request)
    {
        return redirect()->route('admin.service-paket.create');
    }

    /**
     * Edit data via form terpadu.
     */
    public function edit(Jasa $jasa)
    {
        $servicePaket = ServicePaket::where('nama', $jasa->nama_jasa)->first();
        if ($servicePaket) {
            return redirect()->route('admin.service-paket.edit', $servicePaket->id);
        }

        return redirect()->route('admin.jasa.index');
    }

    /**
     * Update data.
     */
    public function update(Request $request, Jasa $jasa)
    {
        return redirect()->route('admin.jasa.index');
    }

    /**
     * Hapus data jasa.
     */
    public function destroy(Jasa $jasa)
    {
        $servicePaket = ServicePaket::where('nama', $jasa->nama_jasa)->first();
        if ($servicePaket && !$servicePaket->services()->exists()) {
            $servicePaket->peralatans()->detach();
            $servicePaket->delete();
        }

        $jasa->delete();

        return redirect()
            ->route('admin.jasa.index')
            ->with('success', 'Data jasa layanan berhasil dihapus.');
    }
}
