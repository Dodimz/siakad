<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\KerjaPraktek;
use App\Models\LogbookKp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KpController extends Controller
{
    public function index()
    {
        $dosen = Auth::user()->dosen;
        $kpList = KerjaPraktek::where('pembimbing_id', $dosen->id)
            ->with(['mahasiswa.user'])
            ->orderByRaw("CASE status WHEN 'berlangsung' THEN 0 WHEN 'pengajuan' THEN 1 WHEN 'disetujui' THEN 2 WHEN 'selesai_kp' THEN 3 WHEN 'penyusunan_laporan' THEN 4 WHEN 'seminar' THEN 5 WHEN 'revisi' THEN 6 WHEN 'selesai' THEN 7 WHEN 'ditolak' THEN 8 ELSE 99 END")
            ->get();

        $pendingLogbook = LogbookKp::whereHas('kerjaPraktek', fn ($q) => $q->where('pembimbing_id', $dosen->id))
            ->where('status', LogbookKp::STATUS_PENDING)
            ->count();

        return view('dosen.kp.index', compact('dosen', 'kpList', 'pendingLogbook'));
    }

    public function show(KerjaPraktek $kp)
    {
        $dosen = Auth::user()->dosen;
        if ($kp->pembimbing_id !== $dosen->id) {
            abort(403);
        }

        $kp->load(['mahasiswa.user', 'pembimbing.user', 'logbook']);

        return view('dosen.kp.show', compact('dosen', 'kp'));
    }

    public function reviewLogbook(Request $request, LogbookKp $logbook)
    {
        $dosen = Auth::user()->dosen;
        if ($logbook->kerjaPraktek->pembimbing_id !== $dosen->id) {
            abort(403);
        }

        $validated = $request->validate([
            'catatan_pembimbing' => 'nullable|string',
            'status' => 'required|in:disetujui,revisi',
        ]);

        $logbook->update($validated);

        return redirect()->back()->with('success', 'Logbook berhasil direview');
    }

    public function updateStatus(Request $request, KerjaPraktek $kp)
    {
        $dosen = Auth::user()->dosen;
        if ($kp->pembimbing_id !== $dosen->id) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => 'required|in:'.implode(',', array_keys(KerjaPraktek::getStatusList())),
        ]);

        $kp->update(['status' => $validated['status']]);

        return redirect()->back()->with('success', 'Status berhasil diupdate');
    }
}
