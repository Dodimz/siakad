<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
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
            'users' => User::count(),
            'fakultas' => Fakultas::count(),
            'prodi' => Prodi::count(),
            'mahasiswa' => Mahasiswa::count(),
            'dosen' => Dosen::count(),
            'mata_kuliah' => MataKuliah::count(),
            'kelas' => Kelas::count(),
        ];

        $perKampus = Kampus::query()
            ->withCount(['fakultas', 'prodi', 'mahasiswa', 'dosen', 'users'])
            ->orderBy('nama')
            ->get();

        return view('central.dashboard', compact('stats', 'perKampus'));
    }
}
