<x-app-layout>
    <x-slot name="header">
        Pengaturan Aplikasi
    </x-slot>

    @if(session('success'))
    <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 dark:bg-green-900/20 dark:border-green-700 dark:text-green-400 rounded-lg">{{ session('success') }}</div>
    @endif
    @if($errors->any())
    <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 dark:bg-red-900/20 dark:border-red-700 dark:text-red-400 rounded-lg">
        @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
    </div>
    @endif

    <div class="mb-6">
        <p class="text-sm text-siakad-secondary dark:text-gray-400">Pengaturan global disimpan di database (tabel <code>settings</code>) dan di-merge ke <code>config('siakad.*')</code> saat boot — tidak perlu mengubah file config.</p>
    </div>

    <form method="POST" action="{{ route('central.settings.update') }}" enctype="multipart/form-data">
        @csrf @method('PUT')

        <div class="card-saas p-6 dark:bg-gray-800 mb-6">
            <h2 class="text-base font-semibold text-siakad-dark dark:text-white mb-1">Umum</h2>
            <p class="text-sm text-siakad-secondary dark:text-gray-400 mb-5">Nama aplikasi, versi, dan preferensi halaman.</p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-siakad-dark dark:text-white mb-1.5">Nama Aplikasi</label>
                    <input type="text" name="settings[app_name]" value="{{ old('settings.app_name', \App\Support\Settings::get('app_name')) }}" class="input-saas px-4 py-2 text-sm w-full dark:bg-gray-900 dark:border-gray-700 dark:text-gray-300">
                </div>
                <div>
                    <label class="block text-sm font-medium text-siakad-dark dark:text-white mb-1.5">Versi</label>
                    <input type="text" name="settings[version]" value="{{ old('settings.version', \App\Support\Settings::get('version')) }}" class="input-saas px-4 py-2 text-sm w-full dark:bg-gray-900 dark:border-gray-700 dark:text-gray-300">
                </div>
                <div>
                    <label class="block text-sm font-medium text-siakad-dark dark:text-white mb-1.5">Kapasitas Kelas Default</label>
                    <input type="number" name="settings[kelas_kapasitas_default]" value="{{ old('settings.kelas_kapasitas_default', \App\Support\Settings::get('kelas_kapasitas_default')) }}" class="input-saas px-4 py-2 text-sm w-full dark:bg-gray-900 dark:border-gray-700 dark:text-gray-300">
                </div>
                <div>
                    <label class="block text-sm font-medium text-siakad-dark dark:text-white mb-1.5">Jumlah Data per Halaman</label>
                    <input type="number" name="settings[pagination]" value="{{ old('settings.pagination', \App\Support\Settings::get('pagination')) }}" class="input-saas px-4 py-2 text-sm w-full dark:bg-gray-900 dark:border-gray-700 dark:text-gray-300">
                </div>
                <div>
                    <label class="block text-sm font-medium text-siakad-dark dark:text-white mb-1.5">Format Tanggal</label>
                    <input type="text" name="settings[format_tanggal]" value="{{ old('settings.format_tanggal', \App\Support\Settings::get('format_tanggal')) }}" class="input-saas px-4 py-2 text-sm w-full dark:bg-gray-900 dark:border-gray-700 dark:text-gray-300">
                </div>
            </div>
        </div>

        <div class="card-saas p-6 dark:bg-gray-800 mb-6">
            <h2 class="text-base font-semibold text-siakad-dark dark:text-white mb-1">Label Semester</h2>
            <p class="text-sm text-siakad-secondary dark:text-gray-400 mb-5">Label untuk semester ganjil & genap, dipakai di seluruh tampilan.</p>

            @php $labels = \App\Support\Settings::get('semester_labels', [1 => 'Ganjil', 2 => 'Genap']); @endphp
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-siakad-dark dark:text-white mb-1.5">Semester 1</label>
                    <input type="text" name="settings[semester_labels][1]" value="{{ old('settings.semester_labels.1', $labels[1] ?? 'Ganjil') }}" class="input-saas px-4 py-2 text-sm w-full dark:bg-gray-900 dark:border-gray-700 dark:text-gray-300">
                </div>
                <div>
                    <label class="block text-sm font-medium text-siakad-dark dark:text-white mb-1.5">Semester 2</label>
                    <input type="text" name="settings[semester_labels][2]" value="{{ old('settings.semester_labels.2', $labels[2] ?? 'Genap') }}" class="input-saas px-4 py-2 text-sm w-full dark:bg-gray-900 dark:border-gray-700 dark:text-gray-300">
                </div>
            </div>
        </div>

        <div class="card-saas p-6 dark:bg-gray-800 mb-6" x-data="{ rows: @js(\App\Support\Settings::get('nilai_konversi', [])) }" x-show="true">
            <div class="flex items-center justify-between mb-1">
                <h2 class="text-base font-semibold text-siakad-dark dark:text-white">Konversi Nilai</h2>
                <button type="button" class="btn-ghost-saas px-3 py-1.5 rounded-lg text-xs font-medium dark:text-white" x-on:click="rows.push({ min: 0, max: 0, huruf: '', bobot: 0 })">
                    + Tambah Baris
                </button>
            </div>
            <p class="text-sm text-siakad-secondary dark:text-gray-400 mb-5">Urutan rentang nilai huruf (diproses dari atas ke bawah).</p>

            <div class="space-y-2">
                <template x-for="(row, i) in rows" :key="i">
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-3 items-center">
                        <input type="number" step="0.01" :name="'settings[nilai_konversi][' + i + '][min]'" x-model="row.min" placeholder="Min" class="input-saas px-3 py-2 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-300">
                        <input type="number" step="0.01" :name="'settings[nilai_konversi][' + i + '][max]'" x-model="row.max" placeholder="Max" class="input-saas px-3 py-2 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-300">
                        <input type="text" :name="'settings[nilai_konversi][' + i + '][huruf]'" x-model="row.huruf" placeholder="Huruf" class="input-saas px-3 py-2 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-300">
                        <input type="number" step="0.01" :name="'settings[nilai_konversi][' + i + '][bobot]'" x-model="row.bobot" placeholder="Bobot" class="input-saas px-3 py-2 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-300">
                        <button type="button" class="p-2 text-siakad-secondary hover:text-red-600 hover:bg-red-50 rounded-lg transition justify-self-end" x-on:click="rows.splice(i, 1)" title="Hapus baris">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </div>
                </template>
                <p x-show="rows.length === 0" class="text-xs text-siakad-secondary dark:text-gray-400">Belum ada baris konversi.</p>
            </div>
        </div>

        <div class="card-saas p-6 dark:bg-gray-800 mb-6">
            <h2 class="text-base font-semibold text-siakad-dark dark:text-white mb-1">Batas Maksimum SKS</h2>
            <p class="text-sm text-siakad-secondary dark:text-gray-400 mb-5">Batasan SKS default dan per rentang IPS (dipakai saat kalibrasi jumlah SKS yang boleh diambil mahasiswa).</p>

            <div class="mb-5">
                <label class="block text-sm font-medium text-siakad-dark dark:text-white mb-1.5">SKS Default</label>
                <input type="number" name="settings[maks_sks][default]" value="{{ old('settings.maks_sks.default', \App\Support\Settings::get('maks_sks')['default'] ?? 24) }}" class="input-saas px-4 py-2 text-sm w-full sm:w-48 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-300">
            </div>

            <p class="block text-sm font-medium text-siakad-dark dark:text-white mb-1.5">Aturan per IPS</p>
            <div x-data="{ rows: @js(\App\Support\Settings::get('maks_sks')['ips_rules'] ?? []) }">
                <div class="flex justify-end mb-2">
                    <button type="button" class="btn-ghost-saas px-3 py-1.5 rounded-lg text-xs font-medium dark:text-white" x-on:click="rows.push({ min: 0, max: 0, sks: 18 })">
                        + Tambah Baris
                    </button>
                </div>
                <div class="space-y-2">
                    <template x-for="(row, i) in rows" :key="i">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 items-center">
                            <input type="number" step="0.01" :name="'settings[maks_sks][ips_rules][' + i + '][min]'" x-model="row.min" placeholder="IPS Min" class="input-saas px-3 py-2 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-300">
                            <input type="number" step="0.01" :name="'settings[maks_sks][ips_rules][' + i + '][max]'" x-model="row.max" placeholder="IPS Max" class="input-saas px-3 py-2 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-300">
                            <input type="number" :name="'settings[maks_sks][ips_rules][' + i + '][sks]'" x-model="row.sks" placeholder="SKS" class="input-saas px-3 py-2 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-300">
                            <button type="button" class="p-2 text-siakad-secondary hover:text-red-600 hover:bg-red-50 rounded-lg transition justify-self-end" x-on:click="rows.splice(i, 1)" title="Hapus baris">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </div>
                    </template>
                    <p x-show="rows.length === 0" class="text-xs text-siakad-secondary dark:text-gray-400">Belum ada aturan per IPS.</p>
                </div>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="btn-primary-saas px-6 py-2.5 rounded-lg text-sm font-medium">Simpan Pengaturan</button>
        </div>
    </form>
</x-app-layout>