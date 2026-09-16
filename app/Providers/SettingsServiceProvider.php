<?php

namespace App\Providers;

use App\Support\Settings;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class SettingsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap layanan: nilai setting dari database di-merge ke config('siakad.*').
     * File config/siakad.php berperan sebagai default saat belum disimpan.
     */
    public function boot(): void
    {
        if (! Schema::hasTable('settings')) {
            return;
        }

        Settings::refresh();
    }
}
