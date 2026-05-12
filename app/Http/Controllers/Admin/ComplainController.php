<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Complain;
use App\Models\Pesanan;
use App\Models\Service;
use Illuminate\Http\Request;

class ComplainController extends Controller
{
    public function index(Request $request)
    {
        $query = Complain::with(['pelanggan', 'pesanan', 'service']);

        if ($request->filled('status')) {
            $query->where('status_penyelesaian', $request->status);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('isi_complain', 'like', '%' . $request->search . '%')
                    ->orWhereHas('pelanggan', fn ($q2) => $q2->where('nama', 'like', '%' . $request->search . '%'));
            });
        }

        $complains = $query->latest()->paginate(15)->withQueryString();
        $counts = [
            'total' => Complain::count(),
            'menunggu' => Complain::where('status_penyelesaian', 'menunggu')->count(),
            'diproses' => Complain::where('status_penyelesaian', 'diproses')->count(),
            'selesai' => Complain::where('status_penyelesaian', 'selesai')->count(),
        ];

        return view('admin.complain.index', compact('complains', 'counts'));
    }

    public function update(Request $request, Complain $complain)
    {
        $request->validate([
            'status_penyelesaian' => 'required|in:menunggu,diproses,selesai',
            'service_id' => 'nullable|exists:services,id',
            'pesanan_id' => 'nullable|exists:pesanans,id',
        ]);

        $complain->update([
            'status_penyelesaian' => $request->status_penyelesaian,
            'service_id' => $request->service_id,
            'pesanan_id' => $request->pesanan_id ?: $complain->pesanan_id,
        ]);

        return back()->with('success', 'Status komplain berhasil diperbarui.');
    }

    public function destroy(Complain $complain)
    {
        $complain->delete();
        return back()->with('success', 'Komplain berhasil dihapus.');
    }
}