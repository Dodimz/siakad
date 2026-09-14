<x-guest-layout>
    <div class="text-center px-6">
        <h1 class="text-9xl font-bold text-red-500">500</h1>
        <h2 class="text-2xl font-semibold text-white mt-4">Terjadi Kesalahan Server</h2>
        <p class="text-siakad-300 mt-2 max-w-md">
            Maaf, terjadi kesalahan pada server. Tim kami telah diberitahu dan sedang menangani masalah ini.
        </p>
        <div class="flex gap-4 justify-center mt-6">
            <a href="{{ route('home') }}"
               class="inline-flex items-center gap-2 px-6 py-3 bg-siakad-600 hover:bg-siakad-500 text-white rounded-lg transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                Kembali ke Dashboard
            </a>
            <button onclick="location.reload()"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-gray-700 hover:bg-gray-600 text-white rounded-lg transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Coba Lagi
            </button>
        </div>
    </div>
</x-guest-layout>
