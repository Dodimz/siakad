<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class Settings
{
    public const CACHE_KEY = 'siakad_settings';

    /**
     * Semua setting yang tersimpan di database, berformat key => value.
     */
    public static function all(): array
    {
        return Cache::remember(self::CACHE_KEY, now()->addHours(6), function () {
            return Setting::query()
                ->pluck('value', 'key')
                ->map(fn ($value) => $value ?? [])
                ->toArray();
        });
    }

    /**
     * Ambil nilai setting. Fallback ke config('siakad.*') bila belum disimpan.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $value = self::all()[$key] ?? null;

        return $value ?? config("siakad.$key", $default);
    }

    /**
     * Simpan banyak setting sekaligus, lalu sinkronkan kembali ke config.
     */
    public static function setMany(array $values): void
    {
        foreach ($values as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        self::refresh();
    }

    /**
     * Singkirkan cache dan tulis ulang config agar nilai logis seketika.
     */
    public static function refresh(): void
    {
        Cache::forget(self::CACHE_KEY);

        foreach (self::all() as $key => $value) {
            config()->set("siakad.$key", $value);
        }
    }
}
