<x-app-layout>
    <x-slot name="header">
        Detail KRS - {{ $krs->mahasiswa->user->name ?? 'Unknown' }}
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Student Info -->
        <div class="lg:col-span-1">
            <div class="card-saas p-6 sticky top-24 dark:bg-gray-800">
                <div class="text-center mb-6">
                    <div class="w-20 h-20 rounded-full bg-gradient-to-br from-siakad-primary to-siakad-dark flex items-center justify-center text-white text-2xl font-bold mx-auto mb-4">
                        {{ strtoupper(substr($krs->mahasiswa->user->name ?? 'X', 0, 1)) }}
                    </div>
                    <h3 class="text-xl font-bold text-siakad-dark dark:text-white">{{ $krs->mahasiswa->user->name ?? '-' }}</h3>
                    <p class="text-siakad-secondary dark:text-gray-400">{{ $krs->mahasiswa->nim ?? '-' }}</p>
                </div>

                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-siakad-secondary dark:text-gray-400">Prodi</span>
                        <span class="font-medium text-siakad-dark dark:text-white">{{ $krs->mahasiswa->prodi->nama ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-siakad-secondary dark:text-gray-400">Angkatan</span>
                        <span class="font-medium text-siakad-dark dark:text-white">{{ $krs->mahasiswa->angkatan ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-siakad-secondary dark:text-gray-400">Tahun Akademik</span>
                        <span class="font-medium text-siakad-dark dark:text-white">{{ $krs->tahunAkademik->tahun ?? '-' }} {{ $krs->tahunAkademik->semester ?? '' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-siakad-secondary dark:text-gray-400">Total SKS</span>
                        <span class="font-bold text-siakad-primary dark:text-blue-400 text-lg">{{ $totalSks }}</span>
                    </div>
                </div>

                <div class="mt-6 pt-6 border-t border-siakad-light dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <span class="text-siakad-secondary dark:text-gray-400">Status KRS</span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium capitalize
                            {{ $krs->status == 'approved' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300 dark:border dark:border-emerald-500/20' : 
                               ($krs->status == 'pending' ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300 dark:border dark:border-amber-500/20' : 
                               ($krs->status == 'rejected' ? 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300 dark:border dark:border-red-500/20' : 'bg-slate-100 text-slate-800 dark:bg-gray-700 dark:text-gray-300 dark:border dark:border-gray-600')) }}">
                            {{ $krs->status }}
                        </span>
                    </div>
                </div>

                <a href="{{ tenant_url('admin/krs-approval') }}" class="mt-6 block text-center text-sm text-siakad-secondary dark:text-gray-400 hover:text-siakad-primary dark:hover:text-blue-400 transition">
                    ← Kembali ke daftar
                </a>
            </div>
        </div>

        <!-- Course List -->
        <div class="lg:col-span-2">
            <div class="card-saas overflow-hidden dark:bg-gray-800">
                <div class="p-6 border-b border-siakad-light dark:border-gray-700">
                    <h3 class="text-lg font-bold text-siakad-dark dark:text-white">Mata Kuliah Diambil</h3>
                    <p class="text-sm text-siakad-secondary dark:text-gray-400">{{ $krs->krsDetail->count() }} mata kuliah</p>
                </div>

                <div class="divide-y divide-siakad-light dark:divide-gray-700">
                    @forelse($krs->krsDetail as $detail)
                    <div class="p-4 flex items-center gap-4 hover:bg-siakad-light/30 dark:hover:bg-gray-700/50 transition">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-siakad-primary to-siakad-dark flex items-center justify-center text-white font-bold text-lg">
                            {{ $detail->kelas->nama_kelas ?? '-' }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-siakad-dark dark:text-white">{{ $detail->kelas->mataKuliah->nama_mk ?? '-' }}</p>
                            <p class="text-sm text-siakad-secondary dark:text-gray-400">{{ $detail->kelas->mataKuliah->kode_mk ?? '-' }} • {{ $detail->kelas->dosen->user->name ?? '-' }}</p>
                        </div>
                        <div class="text-center">
                            <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-siakad-primary/10 text-siakad-primary dark:bg-blue-500/20 dark:text-blue-400 font-bold">
                                {{ $detail->kelas->mataKuliah->sks ?? 0 }}
                            </span>
                            <p class="text-xs text-siakad-secondary dark:text-gray-400 mt-1">SKS</p>
                        </div>
                    </div>
                    @empty
                    <div class="p-8 text-center text-siakad-secondary dark:text-gray-400">
                        Tidak ada mata kuliah terdaftar
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
