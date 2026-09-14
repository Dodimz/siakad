<x-app-layout>
    <x-slot name="header">
        Edit Kampus
    </x-slot>

    <div class="max-w-2xl">
        <div class="card-saas p-6 dark:bg-gray-800">
            <h3 class="font-semibold text-siakad-dark dark:text-white mb-6">Informasi Kampus</h3>

            @if($errors->any())
            <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-lg">
                <ul class="list-disc list-inside text-sm text-red-700 dark:text-red-400 space-y-1">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form action="{{ route('central.kampus.update', $kampus) }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-medium text-siakad-dark dark:text-gray-300 mb-2">Nama Kampus</label>
                    <input type="text" name="nama" value="{{ old('nama', $kampus->nama) }}" class="input-saas w-full px-4 py-2.5 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white" required>
                    @error('nama') <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-siakad-dark dark:text-gray-300 mb-2">Slug (kosongkan untuk otomatis)</label>
                    <input type="text" name="slug" value="{{ old('slug', $kampus->slug) }}" class="input-saas w-full px-4 py-2.5 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white">
                    @error('slug') <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-siakad-dark dark:text-gray-300 mb-2">Kode</label>
                        <input type="text" name="kode" value="{{ old('kode', $kampus->kode) }}" class="input-saas w-full px-4 py-2.5 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white" required>
                        @error('kode') <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-siakad-dark dark:text-gray-300 mb-2">Status</label>
                        <select name="status" class="input-saas w-full px-4 py-2.5 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white">
                            @foreach(\App\Models\Kampus::getStatusList() as $value => $label)
                            <option value="{{ $value }}" {{ old('status', $kampus->status) == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-siakad-dark dark:text-gray-300 mb-2">Email</label>
                    <input type="email" name="email" value="{{ old('email', $kampus->email) }}" class="input-saas w-full px-4 py-2.5 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white">
                    @error('email') <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-siakad-dark dark:text-gray-300 mb-2">Telepon</label>
                    <input type="text" name="telepon" value="{{ old('telepon', $kampus->telepon) }}" class="input-saas w-full px-4 py-2.5 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white">
                    @error('telepon') <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-siakad-dark dark:text-gray-300 mb-2">Alamat</label>
                    <textarea name="alamat" rows="3" class="input-saas w-full px-4 py-2.5 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white">{{ old('alamat', $kampus->alamat) }}</textarea>
                </div>

                <div class="pt-4 border-t border-siakad-light dark:border-gray-700 flex items-center justify-end gap-3">
                    <a href="{{ route('central.kampus.index') }}" class="btn-ghost-saas px-4 py-2 rounded-lg text-sm font-medium dark:text-white">Batal</a>
                    <button type="submit" class="btn-primary-saas px-4 py-2 rounded-lg text-sm font-medium">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>