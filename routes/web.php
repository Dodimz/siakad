<?php

use App\Models\Kampus;
use App\Support\TenantManager;
use Illuminate\Support\Facades\Route;

// Root: arahkan ke halaman login kampus aktif pertama
Route::get('/', function () {
    $kampus = Kampus::active()->orderBy('id')->first();

    if (! $kampus) {
        return redirect()->route('login');
    }

    app(TenantManager::class)->set($kampus);

    return redirect(tenant_route('login'));
})->name('home');

// Health endpoints tetap global (tanpa prefix kampus)
require __DIR__.'/health.php';

// Panel pusat superadmin (global, mengelola seluruh kampus)
require __DIR__.'/central/index.php';

// Semua rute aplikasi berpola tenant: /{kampus}/...
// {kampus} wajib, EnsureKampus middleware akan me-resolve & bind tenant.
Route::group([
    'prefix' => '{kampus}',
    'where' => ['kampus' => '[a-z0-9-]+'],
    'middleware' => ['tenant'],
], function () {
    // Redirect dashboard generik ke dashboard sesuai role
    Route::get('dashboard', function () {
        $routes = [
            'superadmin' => 'admin.dashboard',
            'admin_fakultas' => 'admin.dashboard',
            'admin_kampus' => 'admin.dashboard',
            'dosen' => 'dosen.dashboard',
            'mahasiswa' => 'mahasiswa.dashboard',
        ];

        $role = auth()->user()->role;

        return redirect()->route($routes[$role] ?? 'login');
    })
        ->middleware(['auth', 'verified'])
        ->name('dashboard');

    require __DIR__.'/auth.php';
    require __DIR__.'/notification.php';
    require __DIR__.'/profile.php';
    require __DIR__.'/admin/index.php';
    require __DIR__.'/dosen/index.php';
    require __DIR__.'/mahasiswa/index.php';
});
