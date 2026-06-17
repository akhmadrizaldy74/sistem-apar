<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JenisRefill;
use Illuminate\Http\Request;

class JenisRefillController extends Controller
{
    public function index()
    {
        $jenisRefills = JenisRefill::withCount('refills')->latest()->get();

        return view('admin.jenis-refill.index', compact('jenisRefills'));
    }

    public function create()
    {
        return view('admin.jenis-refill.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255|unique:jenis_refills,nama',
            'satuan' => 'required|string|max:20',
            'harga' => 'required|numeric|min:0',
        ]);

        JenisRefill::create([
            'nama' => $request->nama,
            'stok' => 0,
            'satuan' => 'kg',
            'harga' => $request->harga,
        ]);

        return redirect()->route('admin.jenis-refill.index')->with('success', 'Jenis refil berhasil ditambahkan.');
    }

    public function show(JenisRefill $jenisRefill)
    {
        return redirect()->route('admin.jenis-refill.edit', $jenisRefill);
    }

    public function edit(JenisRefill $jenisRefill)
    {
        return view('admin.jenis-refill.edit', compact('jenisRefill'));
    }

    public function update(Request $request, JenisRefill $jenisRefill)
    {
        $request->validate([
            'nama' => 'required|string|max:255|unique:jenis_refills,nama,'.$jenisRefill->id,
            'satuan' => 'required|string|max:20',
            'harga' => 'required|numeric|min:0',
        ]);

        $jenisRefill->update([
            'nama' => $request->nama,
            'satuan' => 'kg',
            'harga' => $request->harga,
        ]);

        return redirect()->route('admin.jenis-refill.index')->with('success', 'Jenis refil berhasil diperbarui.');
    }

    public function destroy(JenisRefill $jenisRefill)
    {
        $jenisRefill->delete();

        return redirect()->route('admin.jenis-refill.index')->with('success', 'Jenis refil berhasil dihapus.');
    }
}
