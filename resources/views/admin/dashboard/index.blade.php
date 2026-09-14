<x-app-layout>
    <x-slot name="header">
        @if($isSuperAdmin)
            Admin Dashboard
        @else
            Dashboard {{ $fakultas?->nama ?? 'Fakultas' }}
        @endif
    </x-slot>

    <!-- Faculty Info Banner for admin_fakultas -->
    @if(!$isSuperAdmin && $fakultas)
    <div class="mb-6 p-4 bg-gradient-to-r from-siakad-primary to-siakad-dark rounded-xl">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
            </div>
            <div>
                <h2 class="text-lg font-semibold text-white">{{ $fakultas->nama }}</h2>
                <p class="text-sm text-white/80">Tahun Akademik: {{ $activeYear?->tahun ?? '-' }} {{ ucfirst($activeYear?->semester ?? '') }}</p>
            </div>
        </div>
    </div>
    @endif

    <!-- Stats Grid -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-{{ $isSuperAdmin ? '6' : '5' }} gap-4 mb-8">
        @if($isSuperAdmin)
        <div class="card-saas p-5 dark:bg-gray-800">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-siakad-primary/10 dark:bg-blue-500/20 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-siakad-primary dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-siakad-dark dark:text-white">{{ $stats['fakultas'] }}</p>
                    <p class="text-xs text-siakad-secondary dark:text-gray-400">Fakultas</p>
                </div>
            </div>
        </div>
        @endif

        <div class="card-saas p-5 dark:bg-gray-800">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-siakad-secondary/10 dark:bg-gray-700/50 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-siakad-secondary dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-siakad-dark dark:text-white">{{ $stats['prodi'] }}</p>
                    <p class="text-xs text-siakad-secondary dark:text-gray-400">Program Studi</p>
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
                    <p class="text-2xl font-bold text-siakad-dark dark:text-white">{{ $stats['dosen'] }}</p>
                    <p class="text-xs text-siakad-secondary dark:text-gray-400">Dosen</p>
                </div>
            </div>
        </div>

        <div class="card-saas p-5 dark:bg-gray-800">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-siakad-secondary/10 dark:bg-gray-700/50 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-siakad-secondary dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-siakad-dark dark:text-white">{{ $stats['mata_kuliah'] }}</p>
                    <p class="text-xs text-siakad-secondary dark:text-gray-400">Mata Kuliah</p>
                </div>
            </div>
        </div>

        <div class="card-saas p-5 dark:bg-gray-800">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-siakad-primary/10 dark:bg-blue-500/20 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-siakad-primary dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"></path></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-siakad-dark dark:text-white">{{ $stats['kelas'] }}</p>
                    <p class="text-xs text-siakad-secondary dark:text-gray-400">Kelas</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Grade Distribution -->
        <div class="card-saas p-6 dark:bg-gray-800">
            <h3 class="font-semibold text-siakad-dark dark:text-white mb-4">Distribusi Nilai</h3>
            @if(count($gradeDistribution) > 0)
            <div class="h-48">
                <canvas id="gradeChart"></canvas>
            </div>
            @else
            <div class="h-48 flex items-center justify-center text-siakad-secondary dark:text-gray-500 text-sm">
                Belum ada data nilai
            </div>
            @endif
        </div>

        <!-- Students per Prodi -->
        <div class="card-saas p-6 dark:bg-gray-800">
            <h3 class="font-semibold text-siakad-dark dark:text-white mb-4">Mahasiswa per Prodi</h3>
            <div class="space-y-3 max-h-48 overflow-y-auto">
                @forelse($prodiStats as $prodi)
                <div class="flex items-center justify-between">
                    <span class="text-sm text-siakad-secondary dark:text-gray-400 truncate flex-1 mr-2">{{ $prodi->nama }}</span>
                    <span class="text-sm font-semibold text-siakad-dark dark:text-white">{{ $prodi->mahasiswa_count }}</span>
                </div>
                @empty
                <div class="text-center text-siakad-secondary dark:text-gray-500 text-sm py-4">
                    Belum ada data
                </div>
                @endforelse
            </div>
        </div>

        <!-- Dosen per Prodi - NEW -->
        <div class="card-saas p-6 dark:bg-gray-800">
            <h3 class="font-semibold text-siakad-dark dark:text-white mb-4">Dosen per Prodi</h3>
            <div class="space-y-3 max-h-48 overflow-y-auto">
                @forelse($dosenPerProdi as $prodi)
                <div class="flex items-center justify-between">
                    <span class="text-sm text-siakad-secondary dark:text-gray-400 truncate flex-1 mr-2">{{ $prodi->nama }}</span>
                    <span class="text-sm font-semibold text-siakad-dark dark:text-white">{{ $prodi->dosen_count }}</span>
                </div>
                @empty
                <div class="text-center text-siakad-secondary dark:text-gray-500 text-sm py-4">
                    Belum ada data
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Quick Links -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @if($isSuperAdmin)
        <a href="{{ tenant_url('admin/fakultas') }}" class="card-saas p-4 hover:border-siakad-primary/30 dark:hover:border-blue-500/30 group flex items-center gap-3 dark:bg-gray-800">
            <div class="w-9 h-9 bg-siakad-primary/10 dark:bg-blue-500/20 rounded-lg flex items-center justify-center group-hover:bg-siakad-primary/20 dark:group-hover:bg-blue-500/30 transition">
                <svg class="w-4 h-4 text-siakad-primary dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
            </div>
            <span class="text-sm font-medium text-siakad-dark dark:text-white">Kelola Fakultas</span>
        </a>
        @endif
        <a href="{{ tenant_url('admin/prodi') }}" class="card-saas p-4 hover:border-siakad-primary/30 dark:hover:border-blue-500/30 group flex items-center gap-3 dark:bg-gray-800">
            <div class="w-9 h-9 bg-siakad-secondary/10 dark:bg-gray-700/50 rounded-lg flex items-center justify-center group-hover:bg-siakad-secondary/20 dark:group-hover:bg-gray-600 transition">
                <svg class="w-4 h-4 text-siakad-secondary dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
            </div>
            <span class="text-sm font-medium text-siakad-dark dark:text-white">Kelola Prodi</span>
        </a>
        <a href="{{ tenant_url('admin/mahasiswa') }}" class="card-saas p-4 hover:border-siakad-primary/30 dark:hover:border-blue-500/30 group flex items-center gap-3 dark:bg-gray-800">
            <div class="w-9 h-9 bg-siakad-primary/10 dark:bg-blue-500/20 rounded-lg flex items-center justify-center group-hover:bg-siakad-primary/20 dark:group-hover:bg-blue-500/30 transition">
                <svg class="w-4 h-4 text-siakad-primary dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            </div>
            <span class="text-sm font-medium text-siakad-dark dark:text-white">Kelola Mahasiswa</span>
        </a>
        <a href="{{ tenant_url('admin/dosen') }}" class="card-saas p-4 hover:border-siakad-primary/30 dark:hover:border-blue-500/30 group flex items-center gap-3 dark:bg-gray-800">
            <div class="w-9 h-9 bg-siakad-dark/10 dark:bg-gray-700/50 rounded-lg flex items-center justify-center group-hover:bg-siakad-dark/20 dark:group-hover:bg-gray-600 transition">
                <svg class="w-4 h-4 text-siakad-dark dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
            </div>
            <span class="text-sm font-medium text-siakad-dark dark:text-white">Kelola Dosen</span>
        </a>
    </div>

    @push('scripts')
    @if(count($gradeDistribution) > 0)
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const siakadPrimary = '#234C6A';
        const siakadSecondary = '#456882';
        const siakadDark = '#1B3C53';
        
        // Grade Chart
        const gradeData = @json($gradeDistribution);
        const gradeCtx = document.getElementById('gradeChart').getContext('2d');
        new Chart(gradeCtx, {
            type: 'doughnut',
            data: {
                labels: Object.keys(gradeData),
                datasets: [{
                    data: Object.values(gradeData),
                    backgroundColor: [
                        siakadPrimary,
                        siakadSecondary,
                        siakadDark,
                        '#86c5e0',
                        '#b9dded',
                        '#dceef6',
                        '#E3E3E3'
                    ],
                    borderWidth: 0,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: { boxWidth: 12, padding: 12, font: { size: 11 } }
                    }
                },
                cutout: '60%'
            }
        });
    </script>
    @endif
    @endpush
</x-app-layout>
