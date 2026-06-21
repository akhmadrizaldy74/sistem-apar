<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Testimoni;
use Illuminate\Http\Request;

class TestimoniController extends Controller
{
    public function index(Request $request)
    {
        $query = Testimoni::with('pelanggan');

        if ($request->filled('replied')) {
            if ($request->replied === 'yes') {
                $query->whereNotNull('admin_note');
            } else {
                $query->whereNull('admin_note');
            }
        }

        $testimonis = $query->latest('tanggal')->paginate(15)->withQueryString();
        $counts = [
            'total' => Testimoni::count(),
            'replied' => Testimoni::whereNotNull('admin_note')->count(),
            'unreplied' => Testimoni::whereNull('admin_note')->count(),
        ];

        return view('admin.testimoni.index', compact('testimonis', 'counts'));
    }

    public function store(Request $request)
    {
        return redirect()
            ->route('admin.testimoni.index')
            ->with('error', 'Admin tidak dapat menambahkan testimoni. Testimoni hanya bisa dibuat oleh pelanggan dari akun pelanggan.');
    }

    public function update(Request $request, Testimoni $testimoni)
    {
        $request->validate([
            'admin_note' => 'nullable|string|max:500',
        ]);

        $testimoni->update($request->only('admin_note'));

        return back()->with('success', 'Balasan testimoni berhasil disimpan.');
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
