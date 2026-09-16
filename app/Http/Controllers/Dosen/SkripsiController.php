<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\BimbinganSkripsi;
use App\Models\Dosen;
use App\Models\Skripsi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SkripsiController extends Controller
{
    public function index()
    {
        $dosen = Auth::user()->dosen;

        if (! $dosen) {
            abort(403);
        }

        // Get all skripsi where this dosen is pembimbing
        $skripsiList = Skripsi::where('pembimbing1_id', $dosen->id)
            ->orWhere('pembimbing2_id', $dosen->id)
            ->with(['mahasiswa.user', 'pembimbing1.user', 'pembimbing2.user'])
            ->orderByRaw("CASE status WHEN 'bimbingan' THEN 0 WHEN 'pengajuan' THEN 1 WHEN 'review' THEN 2 WHEN 'seminar_proposal' THEN 3 WHEN 'penelitian' THEN 4 WHEN 'seminar_hasil' THEN 5 WHEN 'sidang' THEN 6 WHEN 'revisi' THEN 7 WHEN 'selesai' THEN 8 WHEN 'ditolak' THEN 9 WHEN 'diterima' THEN 10 ELSE 99 END")
            ->get();

        // Pending bimbingan
        $pendingBimbingan = BimbinganSkripsi::where('dosen_id', $dosen->id)
            ->where('status', BimbinganSkripsi::STATUS_MENUNGGU)
            ->with('skripsi.mahasiswa.user')
            ->count();

        return view('dosen.skripsi.index', compact('dosen', 'skripsiList', 'pendingBimbingan'));
    }

    public function show(Skripsi $skripsi)
    {
        $dosen = Auth::user()->dosen;

        // Verify dosen is pembimbing
        if ($skripsi->pembimbing1_id !== $dosen->id && $skripsi->pembimbing2_id !== $dosen->id) {
            abort(403, 'Anda bukan pembimbing skripsi ini');
        }

        $skripsi->load(['mahasiswa.user', 'pembimbing1.user', 'pembimbing2.user', 'bimbingan.dosen.user']);

        return view('dosen.skripsi.show', compact('dosen', 'skripsi'));
    }

    public function reviewBimbingan(Request $request, BimbinganSkripsi $bimbingan)
    {
        $dosen = Auth::user()->dosen;

        if ($bimbingan->dosen_id !== $dosen->id) {
            abort(403);
        }

        $validated = $request->validate([
            'catatan_dosen' => 'required|string',
            'status' => 'required|in:disetujui,revisi',
        ]);

        $bimbingan->update([
            'catatan_dosen' => $validated['catatan_dosen'],
            'status' => $validated['status'],
        ]);

        return redirect()->back()->with('success', 'Review bimbingan berhasil disimpan');
    }

    public function updateStatus(Request $request, Skripsi $skripsi)
    {
        $dosen = Auth::user()->dosen;

        if ($skripsi->pembimbing1_id !== $dosen->id && $skripsi->pembimbing2_id !== $dosen->id) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => 'required|in:'.implode(',', array_keys(Skripsi::getStatusList())),
        ]);

        $skripsi->update(['status' => $validated['status']]);

        return redirect()->back()->with('success', 'Status skripsi berhasil diupdate');
    }
}
