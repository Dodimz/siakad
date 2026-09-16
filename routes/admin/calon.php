<?php

use App\Http\Controllers\Admin\CalonMahasiswaController;
use App\Http\Controllers\Admin\PmbLogController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:admin', 'fakultas.scope'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/calon-mahasiswa', [CalonMahasiswaController::class, 'index'])->name('calon-mahasiswa.index');
    Route::post('/calon-mahasiswa/{calon}/terima', [CalonMahasiswaController::class, 'accept'])->name('calon-mahasiswa.accept');
    Route::post('/calon-mahasiswa/{calon}/tolak', [CalonMahasiswaController::class, 'reject'])->name('calon-mahasiswa.reject');
    Route::get('/pmb-logs', [PmbLogController::class, 'index'])->name('pmb-logs.index');
});
