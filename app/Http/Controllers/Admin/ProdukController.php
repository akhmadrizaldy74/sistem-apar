<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JenisApar;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProdukController extends Controller
{
    public function index(Request $request)
    {
        $query = Produk::with(['jenisApar', 'stokBatches']);

        if ($request->filled('search')) {
            $query->where('nama', 'like', '%' . $request->search . '%')
                ->orWhere('merek', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('jenis_apar_id')) {
            $query->where('jenis_apar_id', $request->jenis_apar_id);
        }

        $produks = $query->latest()->paginate(15)->withQueryString();
        $jenisApars = JenisApar::all();

        return view('admin.produk.index', compact('produks', 'jenisApars'));
    }

    public function create()
    {
        return redirect()->route('admin.produk.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required',
            'merek' => 'required|string|max:100',
            'harga' => 'required|numeric',
            'jenis_apar_id' => 'required|exists:jenis_apars,id',
            'kapasitas' => 'required|string|max:50',
            'penggunaan' => 'required|string',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->only('nama', 'merek', 'jenis_apar_id', 'kapasitas', 'penggunaan', 'harga');

        if ($request->hasFile('gambar')) {
            $data['gambar'] = $request->file('gambar')->store('produk', 'public');
        }

        Produk::create($data);

        return redirect()->route('admin.produk.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(Produk $produk)
    {
        $jenisApars = JenisApar::all();

        return view('admin.produk.edit', compact('produk', 'jenisApars'));
    }

    public function update(Request $request, Produk $produk)
    {
        $request->validate([
            'nama' => 'required',
            'merek' => 'required|string|max:100',
            'harga' => 'required|numeric',
            'jenis_apar_id' => 'required|exists:jenis_apars,id',
            'kapasitas' => 'required|string|max:50',
            'penggunaan' => 'required|string',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->only('nama', 'merek', 'jenis_apar_id', 'kapasitas', 'penggunaan', 'harga');

        if ($request->hasFile('gambar')) {
            if ($produk->gambar) {
                Storage::disk('public')->delete($produk->gambar);
            }
            $data['gambar'] = $request->file('gambar')->store('produk', 'public');
        }

        $produk->update($data);

        return redirect()->route('admin.produk.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Produk $produk)
    {
        if ($produk->gambar) {
            Storage::disk('public')->delete($produk->gambar);
        }
        $produk->delete();

        return redirect()->route('admin.produk.index')->with('success', 'Produk berhasil dihapus.');
    }
}
