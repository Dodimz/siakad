<x-app-layout>
    <x-slot name="header">
        User Management
    </x-slot>

    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <p class="text-sm text-siakad-secondary dark:text-gray-400">Kelola semua pengguna sistem</p>
        </div>
        <div class="flex items-center gap-3">
            <form method="GET" class="flex items-center gap-3">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama/email..." class="input-saas px-4 py-2 text-sm w-48 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-300">
                <select name="role" class="input-saas px-4 py-2 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-300">
                    <option value="">Semua Role</option>
                    <option value="superadmin" {{ request('role') == 'superadmin' ? 'selected' : '' }}>Superadmin</option>
                    <option value="admin_kampus" {{ request('role') == 'admin_kampus' ? 'selected' : '' }}>Admin Kampus</option>
                    <option value="admin_fakultas" {{ request('role') == 'admin_fakultas' ? 'selected' : '' }}>Admin Fakultas</option>
                    <option value="dosen" {{ request('role') == 'dosen' ? 'selected' : '' }}>Dosen</option>
                    <option value="mahasiswa" {{ request('role') == 'mahasiswa' ? 'selected' : '' }}>Mahasiswa</option>
                </select>
                <button type="submit" class="btn-primary-saas px-4 py-2 rounded-lg text-sm font-medium">Filter</button>
            </form>
            <button onclick="openModal('createModal')" class="btn-primary-saas px-4 py-2 rounded-lg text-sm font-medium inline-flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                Tambah
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 dark:bg-green-900/30 border border-green-400 text-green-700 dark:text-green-400 rounded-lg">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="mb-4 p-4 bg-red-100 dark:bg-red-900/30 border border-red-400 text-red-700 dark:text-red-400 rounded-lg">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>
    @endif

    <div class="card-saas overflow-hidden dark:bg-gray-800">
        <table class="w-full table-saas">
            <thead>
                <tr class="bg-siakad-light/30 dark:bg-gray-900 border-b border-siakad-light dark:border-gray-700">
                    <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary uppercase">Nama</th>
                    <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary uppercase">Email</th>
                    <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary uppercase">Role</th>
                    <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary uppercase">Fakultas</th>
                    <th class="text-right py-3 px-5 text-xs font-semibold text-siakad-secondary uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-siakad-light dark:divide-gray-700">
                @forelse($users as $user)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="py-4 px-5">
                            <div class="text-sm font-medium text-siakad-dark dark:text-white">{{ $user->name }}</div>
                            @if($user->mahasiswa)<div class="text-xs text-siakad-secondary">NIM: {{ $user->mahasiswa->nim }}</div>@endif
                            @if($user->dosen)<div class="text-xs text-siakad-secondary">NIDN: {{ $user->dosen->nidn }}</div>@endif
                        </td>
                        <td class="py-4 px-5 text-sm text-siakad-secondary">{{ $user->email }}</td>
                        <td class="py-4 px-5">
                            @php
                                $roleColors = ['superadmin'=>'bg-purple-100 text-purple-800','admin'=>'bg-blue-100 text-blue-800','admin_kampus'=>'bg-cyan-100 text-cyan-800','admin_fakultas'=>'bg-indigo-100 text-indigo-800','dosen'=>'bg-green-100 text-green-800','mahasiswa'=>'bg-yellow-100 text-yellow-800'];
                            @endphp
                            <span class="inline-flex px-2.5 py-1 text-xs font-medium rounded-full {{ $roleColors[$user->role] ?? 'bg-gray-100 text-gray-800' }}">{{ ucfirst(str_replace('_', ' ', $user->role)) }}</span>
                        </td>
                        <td class="py-4 px-5 text-sm text-siakad-secondary">{{ $user->fakultas?->nama ?? ($user->mahasiswa?->prodi?->fakultas?->nama ?? ($user->dosen?->prodi?->fakultas?->nama ?? '-')) }}</td>
                        <td class="py-4 px-5 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <button onclick="openEditModal({{ json_encode($user) }})" class="p-2 text-blue-600 hover:bg-blue-100 rounded-lg" title="Edit"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg></button>
                                @if($user->id !== auth()->id())
                                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline" onsubmit="return confirm('Yakin?')">@csrf @method('DELETE')
                                        <button type="submit" class="p-2 text-red-600 hover:bg-red-100 rounded-lg" title="Hapus"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-12 text-center text-siakad-secondary">Tidak ada data</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($users->hasPages())<div class="mt-4">{{ $users->links() }}</div>@endif

    <!-- Create Modal -->
    <div id="createModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black/50" onclick="closeModal('createModal')"></div>
            <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-md p-6 max-h-[90vh] overflow-y-auto">
                <h3 class="text-lg font-semibold text-siakad-dark dark:text-white mb-4">Tambah User</h3>
                <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-4">@csrf
                    <div><label class="block text-sm font-medium text-siakad-secondary mb-1">Nama</label><input type="text" name="name" required class="input-saas w-full dark:bg-gray-700 dark:border-gray-600 dark:text-white"></div>
                    <div><label class="block text-sm font-medium text-siakad-secondary mb-1">Email</label><input type="email" name="email" required class="input-saas w-full dark:bg-gray-700 dark:border-gray-600 dark:text-white"></div>
                    <div><label class="block text-sm font-medium text-siakad-secondary mb-1">Password</label><input type="password" name="password" required minlength="8" class="input-saas w-full dark:bg-gray-700 dark:border-gray-600 dark:text-white"></div>
                    <div><label class="block text-sm font-medium text-siakad-secondary mb-1">Role</label>
                        <select name="role" id="createRole" required onchange="toggleFakultasField('create')" class="input-saas w-full dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <option value="superadmin">Superadmin</option><option value="admin_kampus">Admin Kampus</option><option value="admin_fakultas">Admin Fakultas</option><option value="dosen">Dosen</option><option value="mahasiswa">Mahasiswa</option>
                        </select>
                    </div>
                    <div id="createFakultasField" class="hidden"><label class="block text-sm font-medium text-siakad-secondary mb-1">Fakultas</label>
                        <select name="fakultas_id" class="input-saas w-full dark:bg-gray-700 dark:border-gray-600 dark:text-white"><option value="">-- Pilih --</option>@foreach($fakultasList as $f)<option value="{{ $f->id }}">{{ $f->nama }}</option>@endforeach</select>
                    </div>
                    <div class="flex justify-end gap-3"><button type="button" onclick="closeModal('createModal')" class="px-4 py-2 text-sm text-siakad-secondary hover:bg-gray-100 rounded-lg">Batal</button><button type="submit" class="btn-primary-saas px-4 py-2 rounded-lg text-sm">Simpan</button></div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black/50" onclick="closeModal('editModal')"></div>
            <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-md p-6 max-h-[90vh] overflow-y-auto">
                <h3 class="text-lg font-semibold text-siakad-dark dark:text-white mb-4">Edit User</h3>
                <form id="editForm" method="POST" class="space-y-4">@csrf @method('PUT')
                    <div><label class="block text-sm font-medium text-siakad-secondary mb-1">Nama</label><input type="text" name="name" id="editName" required class="input-saas w-full dark:bg-gray-700 dark:border-gray-600 dark:text-white"></div>
                    <div><label class="block text-sm font-medium text-siakad-secondary mb-1">Email</label><input type="email" name="email" id="editEmail" required class="input-saas w-full dark:bg-gray-700 dark:border-gray-600 dark:text-white"></div>
                    <div><label class="block text-sm font-medium text-siakad-secondary mb-1">Password (kosongkan jika tidak diubah)</label><input type="password" name="password" minlength="8" class="input-saas w-full dark:bg-gray-700 dark:border-gray-600 dark:text-white"></div>
                    <div><label class="block text-sm font-medium text-siakad-secondary mb-1">Role</label>
                        <select name="role" id="editRole" required onchange="toggleFakultasField('edit')" class="input-saas w-full dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <option value="superadmin">Superadmin</option><option value="admin_kampus">Admin Kampus</option><option value="admin_fakultas">Admin Fakultas</option><option value="dosen">Dosen</option><option value="mahasiswa">Mahasiswa</option>
                        </select>
                    </div>
                    <div id="editFakultasField" class="hidden"><label class="block text-sm font-medium text-siakad-secondary mb-1">Fakultas</label>
                        <select name="fakultas_id" id="editFakultasId" class="input-saas w-full dark:bg-gray-700 dark:border-gray-600 dark:text-white"><option value="">-- Pilih --</option>@foreach($fakultasList as $f)<option value="{{ $f->id }}">{{ $f->nama }}</option>@endforeach</select>
                    </div>
                    <div class="flex justify-end gap-3"><button type="button" onclick="closeModal('editModal')" class="px-4 py-2 text-sm text-siakad-secondary hover:bg-gray-100 rounded-lg">Batal</button><button type="submit" class="btn-primary-saas px-4 py-2 rounded-lg text-sm">Simpan</button></div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openModal(id) { document.getElementById(id).classList.remove('hidden'); }
        function closeModal(id) { document.getElementById(id).classList.add('hidden'); }
        function toggleFakultasField(prefix) {
            const role = document.getElementById(prefix + 'Role').value;
            document.getElementById(prefix + 'FakultasField').classList.toggle('hidden', role !== 'admin_fakultas');
        }
        function openEditModal(user) {
            document.getElementById('editForm').action = `/admin/users/${user.id}`;
            document.getElementById('editName').value = user.name;
            document.getElementById('editEmail').value = user.email;
            document.getElementById('editRole').value = user.role;
            document.getElementById('editFakultasId').value = user.fakultas_id || '';
            toggleFakultasField('edit');
            openModal('editModal');
        }
    </script>
</x-app-layout>
