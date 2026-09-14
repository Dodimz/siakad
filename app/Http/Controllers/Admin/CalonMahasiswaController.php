<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CalonMahasiswa;
use App\Models\Prodi;
use App\Services\CalonAcceptanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CalonMahasiswaController extends Controller
{
    public function __construct(
        protected CalonAcceptanceService $acceptance,
    ) {}

    public function index(Request $request): View
    {
        $query = CalonMahasiswa::with('kampus');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('no_pendaftar', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('prodi_nama', 'like', "%{$search}%");
            });
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $calonList = $query->orderByDesc('created_at')
            ->paginate(config('siakad.pagination', 15))
            ->withQueryString();

        $prodiList = Prodi::with('fakultas')->orderBy('nama')->get();

        return view('admin.calon-mahasiswa.index', compact('calonList', 'prodiList'));
    }

    public function accept(Request $request): RedirectResponse
    {
        $calon = $this->resolveCalon($request);

        $validated = $request->validate([
            'prodi_id' => 'required|exists:prodi,id',
            'email' => ['nullable', 'email', 'max:255'],
        ]);

        $email = $validated['email'] ?? null;

        if (! $email && ! $calon->email) {
            return back()->withErrors(['email' => 'Email calon belum terisi di PMB. Isi kolom email pada formulir terima terlebih dahulu.']);
        }

        try {
            $mahasiswa = $this->acceptance->accept(
                $calon,
                (int) $validated['prodi_id'],
                $email,
            );
        } catch (\RuntimeException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return back()->with('success', "Calon {$calon->nama} diterima. NIM: {$mahasiswa->nim}.");
    }

    public function reject(Request $request): RedirectResponse
    {
        $calon = $this->resolveCalon($request);

        try {
            $this->acceptance->reject($calon);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return back()->with('success', "Calon {$calon->nama} ditolak.");
    }

    /**
     * Binding manual setelah middleware tenant aktif agar scope per-kampus berlaku.
     */
    protected function resolveCalon(Request $request): CalonMahasiswa
    {
        return CalonMahasiswa::findOrFail((int) $request->route('calon'));
    }
}
