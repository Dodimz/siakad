<x-guest-layout>
    <div class="text-center px-6">
        <h1 class="text-9xl font-bold text-siakad-400">404</h1>
        <h2 class="text-2xl font-semibold text-white mt-4">Halaman Tidak Ditemukan</h2>
        <p class="text-siakad-300 mt-2 max-w-md">
            Maaf, halaman yang Anda cari tidak ditemukan atau telah dipindahkan.
        </p>
        <a href="{{ route('home') }}"
           class="inline-flex items-center gap-2 mt-6 px-6 py-3 bg-siakad-600 hover:bg-siakad-500 text-white rounded-lg transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            Kembali ke Dashboard
        </a>
    </div>
</x-guest-layout>
