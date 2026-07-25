<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    /**
     * Tampilkan daftar supplier.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $status = $request->input('status');

        $query = Supplier::query();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('nama_supplier', 'like', "%{$search}%")
                  ->orWhere('kontak_person', 'like', "%{$search}%")
                  ->orWhere('no_telepon', 'like', "%{$search}%")
                  ->orWhere('no_wa', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if (!empty($status) && in_array($status, ['aktif', 'nonaktif'], true)) {
            $query->where('status', $status);
        }

        $suppliers = $query->latest()->paginate(10)->withQueryString();

        return view('admin.supplier.index', compact('suppliers', 'search', 'status'));
    }

    /**
     * Tampilkan form tambah supplier.
     */
    public function create()
    {
        return view('admin.supplier.create');
    }

    /**
     * Simpan data supplier baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_supplier' => 'required|string|max:255',
            'kontak_person' => 'nullable|string|max:255',
            'no_telepon'    => 'nullable|string|max:50',
            'no_wa'         => 'required|string|max:50',
            'email'         => 'nullable|email|max:255',
            'alamat'        => 'nullable|string',
            'status'        => 'required|in:aktif,nonaktif',
        ], [
            'nama_supplier.required' => 'Nama supplier wajib diisi.',
            'no_wa.required'         => 'Nomor WhatsApp wajib diisi.',
            'email.email'            => 'Format email tidak valid.',
            'status.required'        => 'Status wajib dipilih.',
        ]);

        Supplier::create($validated);

        return redirect()
            ->route('admin.suppliers.index')
            ->with('success', 'Data supplier berhasil ditambahkan.');
    }

    /**
     * Tampilkan form edit supplier.
     */
    public function edit(Supplier $supplier)
    {
        return view('admin.supplier.edit', compact('supplier'));
    }

    /**
     * Update data supplier.
     */
    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'nama_supplier' => 'required|string|max:255',
            'kontak_person' => 'nullable|string|max:255',
            'no_telepon'    => 'nullable|string|max:50',
            'no_wa'         => 'required|string|max:50',
            'email'         => 'nullable|email|max:255',
            'alamat'        => 'nullable|string',
            'status'        => 'required|in:aktif,nonaktif',
        ], [
            'nama_supplier.required' => 'Nama supplier wajib diisi.',
            'no_wa.required'         => 'Nomor WhatsApp wajib diisi.',
            'email.email'            => 'Format email tidak valid.',
            'status.required'        => 'Status wajib dipilih.',
        ]);

        $supplier->update($validated);

        return redirect()
            ->route('admin.suppliers.index')
            ->with('success', 'Data supplier berhasil diperbarui.');
    }

    /**
     * Hapus data supplier (soft delete).
     */
    public function destroy(Supplier $supplier)
    {
        $supplier->delete();

        return redirect()
            ->route('admin.suppliers.index')
            ->with('success', 'Data supplier berhasil dihapus.');
    }
}
