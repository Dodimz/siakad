<x-app-layout>
    <x-slot name="header">
        Calon Mahasiswa (PMB)
    </x-slot>

    @if(session('success'))<div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 dark:bg-green-900/20 dark:border-green-700 dark:text-green-400 rounded-lg">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 dark:bg-red-900/20 dark:border-red-700 dark:text-red-400 rounded-lg">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif

    <div class="mb-6">
        <p class="text-sm text-siakad-secondary dark:text-gray-400">Calon mahasiswa yang masuk dari sistem PMB. Terima untuk membuat akun & data mahasiswa aktif.</p>
    </div>

    <div class="mb-6">
        <form method="GET" class="flex flex-col sm:flex-row items-center gap-3 w-full">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, no pendaftar, email, prodi..." class="input-saas px-4 py-2 text-sm w-full sm:w-80 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-300">
            <select name="status" class="input-saas px-4 py-2 text-sm w-full sm:w-44 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-300">
                <option value="">Semua Status</option>
                @foreach(\App\Models\CalonMahasiswa::getStatusList() as $value => $label)
                <option value="{{ $value }}" {{ request('status') == $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn-primary-saas px-4 py-2 rounded-lg text-sm font-medium w-full sm:w-auto">Filter</button>
            @if(request()->anyFilled(['search', 'status']))
            <a href="{{ route('admin.calon-mahasiswa.index') }}" class="btn-ghost-saas px-4 py-2 rounded-lg text-sm font-medium w-full sm:w-auto text-center dark:text-white">Reset</a>
            @endif
        </form>
    </div>

    <div class="card-saas overflow-hidden dark:bg-gray-800">
        <div class="overflow-x-auto">
            <table class="w-full table-saas">
                <thead>
                    <tr class="bg-siakad-light/30 dark:bg-gray-900 border-b border-siakad-light dark:border-gray-700">
                        <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider">Pendaftar</th>
                        <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider">Kontak</th>
                        <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider">Masuk</th>
                        <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider">Prodi</th>
                        <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider w-64">Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($calonList as $calon)
                    <tr class="border-b border-siakad-light/50 dark:border-gray-700/50">
                        <td class="py-4 px-5">
                            <p class="text-sm font-medium text-siakad-dark dark:text-white">{{ $calon->nama }}</p>
                            <p class="text-xs text-siakad-secondary dark:text-gray-400">{{ $calon->no_pendaftar }}</p>
                        </td>
                        <td class="py-4 px-5 text-sm text-siakad-secondary dark:text-gray-400">
                            <p>{{ $calon->email ?? '-' }}</p>
                            <p class="text-xs">{{ $calon->telepon }}</p>
                        </td>
                        <td class="py-4 px-5 text-sm text-siakad-dark dark:text-white">
                            <p>{{ $calon->tahun_masuk }}</p>
                            <p class="text-xs text-siakad-secondary dark:text-gray-400">{{ $calon->jalur ?: '-' }}</p>
                        </td>
                        <td class="py-4 px-5 text-sm text-siakad-dark dark:text-white">{{ $calon->prodi_nama ?: '-' }}</td>
                        <td class="py-4 px-5">
                            @php $c = $calon->status_color; @endphp
                            <span class="inline-flex px-2.5 py-1 text-xs font-medium rounded-full bg-{{ $c }}-100 text-{{ $c }}-700 dark:bg-{{ $c }}-500/20 dark:text-{{ $c }}-400">{{ $calon->status_label }}</span>
                        </td>
                        <td class="py-4 px-5">
                            @if($calon->status === \App\Models\CalonMahasiswa::STATUS_PENDING)
                            <form action="{{ route('admin.calon-mahasiswa.accept', $calon) }}" method="POST" class="space-y-2">
                                @csrf
                                <select name="prodi_id" class="input-saas px-3 py-2 text-xs w-full dark:bg-gray-900 dark:border-gray-700 dark:text-gray-300" required>
                                    <option value="">Pilih Prodi...</option>
                                    @foreach($prodiList as $p)
                                    <option value="{{ $p->id }}">{{ $p->nama }}</option>
                                    @endforeach
                                </select>
                                <input type="email" name="email" value="{{ $calon->email ?? '' }}" placeholder="Email (jika kosong)" class="input-saas px-3 py-2 text-xs w-full dark:bg-gray-900 dark:border-gray-700 dark:text-gray-300">
                                <div class="flex items-center gap-2">
                                    <button type="submit" class="btn-primary-saas px-3 py-1.5 rounded-lg text-xs font-medium">Terima</button>
                                </div>
                            </form>
                            <form action="{{ route('admin.calon-mahasiswa.reject', $calon) }}" method="POST" class="mt-2" onsubmit="return confirm('Tolak calon ini?')">
                                @csrf
                                <button type="submit" class="btn-ghost-saas px-3 py-1.5 rounded-lg text-xs font-medium text-red-600 border border-red-200 dark:border-red-700 hover:bg-red-50 dark:hover:bg-red-900/20">Tolak</button>
                            </form>
                            @else
                            <span class="text-xs text-siakad-secondary dark:text-gray-500">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-siakad-secondary dark:text-gray-400 text-sm">Belum ada calon mahasiswa</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">
        {{ $calonList->links() }}
    </div>
</x-app-layout>