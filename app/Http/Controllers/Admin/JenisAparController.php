<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JenisApar;
use Illuminate\Http\Request;

class JenisAparController extends Controller
{
    public function index()
    {
        $data = JenisApar::all();

        return view('admin.jenis-apar.index', compact('data'));
    }

    public function create()
    {
        return view('admin.jenis-apar.create');
    }

    public function store(Request $request)
    {
        $request->validate(['nama' => 'required']);
        JenisApar::create($request->all());

        return redirect()->route('admin.jenis-apar.index')->with('success', 'Jenis APAR berhasil ditambahkan.');
    }

    public function edit(JenisApar $jenisApar)
    {
        return view('admin.jenis-apar.edit', compact('jenisApar'));
    }

    public function update(Request $request, JenisApar $jenisApar)
    {
        $request->validate(['nama' => 'required']);
        $jenisApar->update($request->all());

        return redirect()->route('admin.jenis-apar.index')->with('success', 'Jenis APAR berhasil diperbarui.');
    }

    public function destroy(JenisApar $jenisApar)
    {
        $jenisApar->delete();

        return redirect()->route('admin.jenis-apar.index')->with('success', 'Jenis APAR berhasil dihapus.');
    }
}
