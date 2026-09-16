<x-app-layout>
    <x-slot name="header">
        Log Import PMB
    </x-slot>

    <div class="mb-6">
        <p class="text-sm text-siakad-secondary dark:text-gray-400">Riwayat pengiriman data calon dari sistem PMB eksternal ke kampus ini.</p>
    </div>

    <div class="card-saas overflow-hidden dark:bg-gray-800">
        <div class="overflow-x-auto">
            <table class="w-full table-saas">
                <thead>
                    <tr class="bg-siakad-light/30 dark:bg-gray-900 border-b border-siakad-light dark:border-gray-700">
                        <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider">Waktu</th>
                        <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider">No. Pendaftar</th>
                        <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider">Nama</th>
                        <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                        <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider">IP Pengirim</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr class="border-b border-siakad-light/50 dark:border-gray-700/50">
                        <td class="py-4 px-5 text-sm text-siakad-secondary dark:text-gray-400">{{ $log->created_at->format('d M Y H:i:s') }}</td>
                        <td class="py-4 px-5 text-sm font-medium text-siakad-dark dark:text-white">{{ $log->no_pendaftar }}</td>
                        <td class="py-4 px-5 text-sm text-siakad-dark dark:text-white">{{ $log->nama }}</td>
                        <td class="py-4 px-5">
                            @php $c = $log->aksi_color; @endphp
                            <span class="inline-flex px-2.5 py-1 text-xs font-medium rounded-full bg-{{ $c }}-100 text-{{ $c }}-700 dark:bg-{{ $c }}-500/20 dark:text-{{ $c }}-400">{{ $log->aksi_label }}</span>
                        </td>
                        <td class="py-4 px-5 text-sm text-siakad-secondary dark:text-gray-400">{{ $log->ip_address ?: '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-12 text-center text-siakad-secondary dark:text-gray-400 text-sm">Belum ada log import PMB</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">
        {{ $logs->links() }}
    </div>
</x-app-layout>