<x-app-layout>
    <x-slot name="header">
        Panel Pusat
    </x-slot>

    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-siakad-dark dark:text-white">Dashboard Multi-Kampus</h2>
            <p class="text-sm text-siakad-secondary dark:text-gray-400">Ringkasan agregat seluruh kampus pada platform.</p>
        </div>
        <a href="{{ route('central.kampus.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-siakad-primary hover:bg-siakad-600 text-white text-sm font-medium rounded-lg transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Tambah Kampus
        </a>
    </div>

    <!-- Stat Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 xl:grid-cols-8 gap-4 mb-8">
        <div class="card-saas p-5 dark:bg-gray-800">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-siakad-primary/10 dark:bg-blue-500/20 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-siakad-primary dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-siakad-dark dark:text-white">{{ $stats['kampus'] }}</p>
                    <p class="text-xs text-siakad-secondary dark:text-gray-400">Kampus</p>
                </div>
            </div>
        </div>

        <div class="card-saas p-5 dark:bg-gray-800">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-emerald-100 dark:bg-emerald-500/20 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ $stats['kampus_active'] }}</p>
                    <p class="text-xs text-siakad-secondary dark:text-gray-400">Aktif</p>
                </div>
            </div>
        </div>

        <div class="card-saas p-5 dark:bg-gray-800">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-amber-100 dark:bg-amber-500/20 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"></path></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ $stats['kampus_trial'] }}</p>
                    <p class="text-xs text-siakad-secondary dark:text-gray-400">Trial</p>
                </div>
            </div>
        </div>

        <div class="card-saas p-5 dark:bg-gray-800">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-red-100 dark:bg-red-500/20 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $stats['kampus_suspended'] }}</p>
                    <p class="text-xs text-siakad-secondary dark:text-gray-400">Suspend</p>
                </div>
            </div>
        </div>

        <div class="card-saas p-5 dark:bg-gray-800">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-siakad-primary/10 dark:bg-blue-500/20 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-siakad-primary dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-siakad-dark dark:text-white">{{ $stats['mahasiswa'] }}</p>
                    <p class="text-xs text-siakad-secondary dark:text-gray-400">Mahasiswa</p>
                </div>
            </div>
        </div>

        <div class="card-saas p-5 dark:bg-gray-800">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-siakad-dark/10 dark:bg-gray-700/50 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-siakad-dark dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-siakad-dark dark:text-white">{{ $stats['users'] }}</p>
                    <p class="text-xs text-siakad-secondary dark:text-gray-400">Total Users</p>
                </div>
            </div>
        </div>

        <div class="card-saas p-5 dark:bg-gray-800">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-violet-100 dark:bg-violet-500/20 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-violet-600 dark:text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-violet-600 dark:text-violet-400">{{ $stats['dosen'] }}</p>
                    <p class="text-xs text-siakad-secondary dark:text-gray-400">Dosen</p>
                </div>
            </div>
        </div>

        <div class="card-saas p-5 dark:bg-gray-800">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-cyan-100 dark:bg-cyan-500/20 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-cyan-600 dark:text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-cyan-600 dark:text-cyan-400">{{ $stats['calon_total'] }}</p>
                    <p class="text-xs text-siakad-secondary dark:text-gray-400">Calon PMB</p>
                    <p class="text-[10px] text-amber-600 dark:text-amber-400">{{ $stats['calon_pending'] }} pending</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Per-Kampus Breakdown -->
    <div class="card-saas p-6 dark:bg-gray-800 overflow-hidden">
        <h3 class="font-semibold text-siakad-dark dark:text-white mb-4">Rincian per Kampus</h3>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs text-siakad-secondary dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">
                        <th class="py-3 px-2 font-semibold">Kampus</th>
                        <th class="py-3 px-2 font-semibold text-center">Status</th>
                        <th class="py-3 px-2 font-semibold text-center">Fakultas</th>
                        <th class="py-3 px-2 font-semibold text-center">Prodi</th>
                        <th class="py-3 px-2 font-semibold text-center">Mahasiswa</th>
                        <th class="py-3 px-2 font-semibold text-center">Dosen</th>
                        <th class="py-3 px-2 font-semibold text-center">Users</th>
                        <th class="py-3 px-2 font-semibold text-center">Calon PMB</th>
                        <th class="py-3 px-2 font-semibold text-center">PMB Token</th>
                        <th class="py-3 px-2 font-semibold text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($perKampus as $kampus)
                    <tr class="border-b border-gray-100 dark:border-gray-700 last:border-0 hover:bg-gray-50 dark:hover:bg-gray-700/30">
                        <td class="py-3 px-2">
                            <p class="font-semibold text-siakad-dark dark:text-white">{{ $kampus->nama }}</p>
                            <p class="text-xs text-siakad-secondary dark:text-gray-400">{{ $kampus->slug }}</p>
                        </td>
                        <td class="py-3 px-2 text-center">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-full bg-{{ $kampus->status_color }}-100 text-{{ $kampus->status_color }}-700 dark:bg-{{ $kampus->status_color }}-500/20 dark:text-{{ $kampus->status_color }}-400">
                                {{ $kampus->status_label }}
                            </span>
                        </td>
                        <td class="py-3 px-2 text-center text-siakad-dark dark:text-white">{{ $kampus->fakultas_count }}</td>
                        <td class="py-3 px-2 text-center text-siakad-dark dark:text-white">{{ $kampus->prodi_count }}</td>
                        <td class="py-3 px-2 text-center text-siakad-dark dark:text-white">{{ $kampus->mahasiswa_count }}</td>
                        <td class="py-3 px-2 text-center text-siakad-dark dark:text-white">{{ $kampus->dosen_count }}</td>
                        <td class="py-3 px-2 text-center text-siakad-dark dark:text-white">{{ $kampus->users_count }}</td>
                        <td class="py-3 px-2 text-center text-siakad-dark dark:text-white">{{ $kampus->calon_mahasiswa_count }}</td>
                        <td class="py-3 px-2 text-center">
                            @if($kampus->pmb_token)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-400">Aktif</span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-500 dark:bg-gray-700/50 dark:text-gray-400">Belum</span>
                            @endif
                        </td>
                        <td class="py-3 px-2 text-center">
                            <a href="{{ route('central.kampus.edit', $kampus) }}" class="inline-flex items-center gap-1 text-siakad-primary dark:text-blue-400 hover:underline text-xs font-medium">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                Kelola
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="py-8 text-center text-siakad-secondary dark:text-gray-500 text-sm">
                            Belum ada kampus terdaftar.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>