<x-app-layout>
    <x-slot name="header">
        <span class="md:hidden">KRS</span>
        <span class="hidden md:inline">Kartu Rencana Studi (KRS)</span>
    </x-slot>

    <!-- Status Banner -->
    <div class="mb-8">
        <div class="bg-siakad-primary rounded-xl p-6 text-white">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                <div>
                    <p class="text-[10px] md:text-xs opacity-70 uppercase tracking-wider">Tahun Akademik Aktif</p>
                    <h3 class="text-xl font-bold mt-1">{{ \App\Models\TahunAkademik::where('is_active', true)->first()?->tahun ?? '-' }} - {{ \App\Models\TahunAkademik::where('is_active', true)->first()?->semester ?? '-' }}</h3>
                </div>
                <div class="flex items-center justify-between md:justify-end gap-6 border-t border-white/10 pt-4 md:border-0 md:pt-0">
                    <div class="text-center">
                        <p class="text-2xl font-bold">{{ $krs->krsDetail->sum(fn($d) => $d->kelas->mataKuliah->sks) }}</p>
                        <p class="text-xs opacity-70">Total SKS</p>
                    </div>
                    <div class="text-center">
                        @php
                            $statusColors = [
                                'approved' => 'bg-emerald-500/30 text-emerald-100',
                                'rejected' => 'bg-red-500/30 text-red-100',
                                'pending' => 'bg-amber-500/30 text-amber-100',
                                'draft' => 'bg-white/20 text-white',
                            ];
                        @endphp
                        <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold {{ $statusColors[$krs->status] ?? 'bg-white/20' }}">
                            {{ ucfirst($krs->status) }}
                        </span>
                    </div>
                </div>
            </div>
            
            @if($krs->status == 'draft')
            <div class="mt-5 pt-5 border-t border-white/20">
                <form action="{{ tenant_url('mahasiswa/krs/submit') }}" method="POST" class="flex items-center justify-between">
                    @csrf
                    <p class="text-sm opacity-80">Setelah diajukan, KRS tidak dapat diubah lagi.</p>
                    <button type="submit" onclick="return confirm('Yakin ingin mengajukan KRS? Anda tidak dapat mengubah lagi setelah ini.')"
                        class="px-5 py-2 bg-white text-siakad-primary rounded-lg font-semibold text-sm hover:bg-siakad-light transition">
                        Ajukan KRS
                    </button>
                </form>
            </div>
            @elseif($krs->status == 'rejected')
            <div class="mt-5 pt-5 border-t border-white/20">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold text-red-200 mb-1">❌ KRS Ditolak</p>
                        <p class="text-sm opacity-80">{{ $krs->catatan ?? 'Silakan revisi KRS Anda dan ajukan kembali.' }}</p>
                    </div>
                    <form action="{{ tenant_url('mahasiswa/krs/revise') }}" method="POST">
                        @csrf
                        <button type="submit" onclick="return confirm('Ubah status KRS menjadi draft untuk direvisi?')"
                            class="px-5 py-2 bg-white text-siakad-primary rounded-lg font-semibold text-sm hover:bg-siakad-light transition whitespace-nowrap">
                            Revisi KRS
                        </button>
                    </form>
                </div>
            </div>
            @elseif($krs->status == 'pending')
            <div class="mt-5 pt-5 border-t border-white/20">
                <p class="text-sm opacity-80">⏳ KRS Anda sedang menunggu persetujuan dari Dosen PA.</p>
            </div>
            @elseif($krs->status == 'approved')
            <div class="mt-5 pt-5 border-t border-white/20">
                <p class="text-sm opacity-80">✅ KRS Anda telah disetujui oleh Dosen PA.</p>
            </div>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Taken Classes -->
        <div class="{{ $krs->status == 'draft' ? 'lg:col-span-2' : 'lg:col-span-3' }}">
            <div class="card-saas overflow-hidden">
                <div class="px-6 py-4 border-b border-siakad-light">
                    <h3 class="font-semibold text-siakad-dark">Mata Kuliah Diambil</h3>
                    <p class="text-xs text-siakad-secondary mt-1">{{ $krs->krsDetail->count() }} mata kuliah dipilih</p>
                </div>
                
                <div class="divide-y divide-siakad-light/50">
                    @forelse($krs->krsDetail as $detail)
                    <div class="p-4 flex items-center gap-4 hover:bg-siakad-light/20 transition">
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-siakad-dark truncate">{{ $detail->kelas->mataKuliah->nama_mk }}</p>
                            <p class="text-xs text-siakad-secondary">{{ $detail->kelas->mataKuliah->kode_mk }} • {{ $detail->kelas->dosen->user->name }}</p>
                        </div>
                        <div class="text-center px-3">
                            <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-siakad-primary/10 text-siakad-primary font-semibold text-sm">
                                {{ $detail->kelas->mataKuliah->sks }}
                            </span>
                            <p class="text-[10px] text-siakad-secondary mt-1">SKS</p>
                        </div>
                        @if($krs->status == 'draft')
                        <form action="{{ tenant_url('mahasiswa/krs/'.$detail->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-2 text-siakad-secondary hover:text-red-500 hover:bg-red-50 rounded-lg transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </form>
                        @endif
                    </div>
                    @empty
                    <div class="py-12 text-center">
                        <div class="w-14 h-14 rounded-xl bg-siakad-light/50 flex items-center justify-center mx-auto mb-3">
                            <svg class="w-7 h-7 text-siakad-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                        </div>
                        <p class="text-siakad-secondary font-medium">Belum ada mata kuliah diambil</p>
                        <p class="text-xs text-siakad-secondary/70">Pilih kelas di samping untuk memulai</p>
                    </div>
                    @endforelse
                </div>
                <!-- Total SKS Footer -->
                <div class="px-6 py-4 border-t border-siakad-light dark:border-slate-700 flex justify-between items-center" style="background-color: var(--bg-card);">
                    <span class="font-semibold text-siakad-dark">Total SKS Diambil</span>
                    <span class="font-bold text-siakad-primary text-lg">{{ $krs->krsDetail->sum(fn($d) => $d->kelas->mataKuliah->sks) }} SKS</span>
                </div>
            </div>
        </div>

        <!-- Available Classes -->
        @if($krs->status == 'draft')
        <div class="lg:col-span-1">
            <div class="card-saas overflow-hidden sticky top-24">
                <div class="px-6 py-4 border-b border-siakad-light">
                    <h3 class="font-semibold text-siakad-dark">Kelas Tersedia</h3>
                    <p class="text-xs text-siakad-secondary mt-1">Pilih kelas untuk diambil</p>
                </div>
                
                <div class="max-h-[60vh] overflow-y-auto">
                    @forelse($availableKelas as $semester => $kelasList)
                    <div x-data="{ open: false }" class="border-b border-siakad-light/50 last:border-b-0">
                        <button @click="open = !open" class="w-full px-4 py-3 bg-siakad-light/30 dark:bg-slate-700/30 flex items-center justify-between hover:bg-siakad-light/50 dark:hover:bg-slate-700/50 transition cursor-pointer">
                            <div class="text-left">
                                <h4 class="font-semibold text-siakad-primary text-sm">{{ $semester }}</h4>
                                <p class="text-[10px] text-siakad-secondary">{{ $kelasList->count() }} kelas tersedia</p>
                            </div>
                            <svg class="w-4 h-4 text-siakad-secondary transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="open" x-collapse class="divide-y divide-siakad-light/30">
                            @foreach($kelasList as $k)
                            <div class="p-4">
                                <div class="flex items-start gap-3">
                                    <div class="flex-1 min-w-0">
                                        <p class="font-medium text-siakad-dark text-sm truncate">{{ $k->mataKuliah->nama_mk }}</p>
                                        <p class="text-[11px] text-siakad-secondary mt-0.5">{{ $k->mataKuliah->sks }} SKS • {{ $k->dosen->user->name ?? '-' }}</p>
                                        <div class="flex items-center gap-2 mt-2">
                                            <div class="flex-1 h-1 bg-siakad-light rounded-full overflow-hidden">
                                                <div class="h-full bg-siakad-primary rounded-full" style="width: {{ min(100, ($k->krsDetail->count() / $k->kapasitas) * 100) }}%"></div>
                                            </div>
                                            <span class="text-[10px] text-siakad-secondary">{{ $k->krsDetail->count() }}/{{ $k->kapasitas }}</span>
                                        </div>
                                    </div>
                                </div>
                                <form action="{{ tenant_url('mahasiswa/krs') }}" method="POST" class="mt-3">
                                    @csrf
                                    <input type="hidden" name="kelas_id" value="{{ $k->id }}">
                                    <button type="submit" class="w-full py-2 px-3 bg-siakad-primary/10 text-siakad-primary rounded-lg font-medium text-sm hover:bg-siakad-primary/20 transition">
                                        + Ambil Kelas
                                    </button>
                                </form>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @empty
                    <div class="p-6 text-center text-siakad-secondary text-sm">
                        Tidak ada kelas tersedia
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
        @endif
    </div>
</x-app-layout>
