<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Testimoni;
use App\Models\Pelanggan;
use Illuminate\Http\Request;

class TestimoniController extends Controller
{
    public function index(Request $request)
    {
        $query = Testimoni::with('pelanggan');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $testimonis = $query->latest('tanggal')->paginate(15)->withQueryString();
        $counts = [
            'total' => Testimoni::count(),
            'pending' => Testimoni::where('status', 'pending')->count(),
            'approved' => Testimoni::where('status', 'approved')->count(),
            'rejected' => Testimoni::where('status', 'rejected')->count(),
        ];
        $pelanggans = Pelanggan::orderBy('nama')->get();

        return view('admin.testimoni.index', compact('testimonis', 'pelanggans', 'counts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'pelanggan_id' => 'required|exists:pelanggans,id',
            'rating'       => 'required|integer|min=1|max=5',
            'review'       => 'required|string',
        ]);

        Testimoni::create([
            'pelanggan_id' => $request->pelanggan_id,
            'rating'       => $request->rating,
            'review'       => $request->review,
            'tanggal'      => now(),
            'status'       => 'approved',
        ]);

        return back()->with('success', 'Testimoni berhasil ditambahkan.');
    }

    public function update(Request $request, Testimoni $testimoni)
    {
        $request->validate([
            'rating' => 'required|integer|min=1|max=5',
            'review' => 'required|string',
            'admin_note' => 'nullable|string|max:500',
        ]);

        $testimoni->update($request->only('rating', 'review', 'admin_note'));

        return back()->with('success', 'Testimoni berhasil diperbarui.');
    }

    public function approve(Testimoni $testimoni)
    {
        $testimoni->update(['status' => 'approved']);
        return back()->with('success', 'Testimoni berhasil disetujui.');
    }

    public function reject(Request $request, Testimoni $testimoni)
    {
        $request->validate([
            'admin_note' => 'nullable|string|max:500',
        ]);

        $testimoni->update([
            'status' => 'rejected',
            'admin_note' => $request->admin_note,
        ]);

        return back()->with('success', 'Testimoni ditolak.');
    }

    public function pending(Testimoni $testimoni)
    {
        $testimoni->update(['status' => 'pending', 'admin_note' => null]);
        return back()->with('success', 'Testimoni dikembalikan ke status menunggu.');
    }

    public function destroy(Testimoni $testimoni)
    {
        ActivityLog::query()
            ->where('log_name', 'feedback')
            ->where('subject_type', Testimoni::class)
            ->where('subject_id', $testimoni->id)
            ->delete();

        $testimoni->delete();
        return back()->with('success', 'Testimoni berhasil dihapus.');
    }
}
