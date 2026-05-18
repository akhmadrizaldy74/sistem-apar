<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Peralatan;
use Illuminate\Http\Request;

class PeralatanController extends Controller
{
    public function index()
    {
        $peralatans = Peralatan::latest()->get();

        return view('admin.peralatan.index', compact('peralatans'));
    }

    public function create()
    {
        return view('admin.peralatan.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'harga_standar' => 'required|numeric|min:0',
        ]);

        Peralatan::create([
            'nama' => $validated['nama'],
            'stok' => 0,
            'harga_standar' => $validated['harga_standar'],
        ]);

        return redirect()
            ->route('admin.peralatan.index')
            ->with('success', 'Master peralatan berhasil ditambahkan. Tambah stok dilakukan dari menu Pengeluaran.');
    }

    public function edit(Peralatan $peralatan)
    {
        return view('admin.peralatan.edit', compact('peralatan'));
    }

    public function update(Request $request, Peralatan $peralatan)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'harga_standar' => 'required|numeric|min:0',
        ]);

        $peralatan->update([
            'nama' => $validated['nama'],
            'harga_standar' => $validated['harga_standar'],
        ]);

        return redirect()
            ->route('admin.peralatan.index')
            ->with('success', 'Master peralatan berhasil diperbarui.');
    }

    public function destroy(Peralatan $peralatan)
    {
        $peralatan->delete();

        return redirect()->route('admin.peralatan.index')->with('success', 'Data peralatan berhasil dihapus.');
    }
}
