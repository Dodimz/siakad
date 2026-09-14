<?php

use App\Http\Controllers\Api\PmbController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth.pmb')
    ->prefix('v1/pmb')
    ->group(function () {
        Route::post('calon', [PmbController::class, 'store'])->name('pmb.calon.store');
    });
