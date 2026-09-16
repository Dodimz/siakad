<?php

use App\Http\Controllers\Central\DashboardController;
use App\Http\Controllers\Central\KampusController;
use App\Http\Controllers\Central\PmbLogController;
use Illuminate\Support\Facades\Route;

// Panel pusat superadmin (global, di luar prefix {kampus})
// set.default.kampus dijalankan lebih dulu agar pembangkit URL tenant
// (termasuk redirect login saat belum login) memiliki konteks kampus.
Route::middleware(['set.default.kampus', 'auth', 'role:superadmin'])
    ->prefix('central')
    ->name('central.')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::resource('kampus', KampusController::class)
            ->except(['show'])
            ->parameters(['kampus' => 'kampus']);
        Route::get('/pmb-logs', [PmbLogController::class, 'index'])->name('pmb-logs.index');
    });
