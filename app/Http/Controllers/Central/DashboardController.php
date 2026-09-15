<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\CalonMahasiswa;
use App\Models\Dosen;
use App\Models\Fakultas;
use App\Models\Kampus;
use App\Models\Kelas;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use App\Models\Prodi;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Tampilkan agregat data seluruh kampus.
     */
    public function index(): View
    {
        $stats = [
            'kampus' => Kampus::count(),
            'kampus_active' => Kampus::where('status', Kampus::STATUS_ACTIVE)->count(),
            'kampus_trial' => Kampus::where('status', Kampus::STATUS_TRIAL)->count(),
            'kampus_suspended' => Kampus::where('status', Kampus::STATUS_SUSPENDED)->count(),
            'pmb_ready' => Kampus::whereNotNull('pmb_token')->count(),
            'users' => User::count(),
            'fakultas' => Fakultas::count(),
            'prodi' => Prodi::count(),
            'mahasiswa' => Mahasiswa::count(),
            'dosen' => Dosen::count(),
            'mata_kuliah' => MataKuliah::count(),
            'kelas' => Kelas::count(),
            'calon_total' => CalonMahasiswa::count(),
            'calon_pending' => CalonMahasiswa::where('status', CalonMahasiswa::STATUS_PENDING)->count(),
            'calon_imported' => CalonMahasiswa::where('status', CalonMahasiswa::STATUS_IMPORTED)->count(),
            'calon_rejected' => CalonMahasiswa::where('status', CalonMahasiswa::STATUS_REJECTED)->count(),
        ];

        $perKampus = Kampus::query()
            ->withCount(['fakultas', 'prodi', 'mahasiswa', 'dosen', 'users', 'calonMahasiswa'])
            ->orderBy('nama')
            ->get();

        return view('central.dashboard', compact('stats', 'perKampus'));
    }
}
