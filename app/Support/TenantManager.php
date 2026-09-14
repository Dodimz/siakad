<?php

namespace App\Support;

use App\Models\Kampus;
use Illuminate\Support\Facades\URL;

class TenantManager
{
    public function __construct(
        protected ?Kampus $current = null,
    ) {}

    public function set(Kampus $kampus): void
    {
        $this->current = $kampus;
    }

    public function clear(): void
    {
        $this->current = null;
    }

    public function current(): ?Kampus
    {
        return $this->current;
    }

    /**
     * Apakah request sedang berjalan di dalam konteks tenant (kampus tertentu).
     */
    public function isSetup(): bool
    {
        return $this->current !== null;
    }

    public function id(): ?int
    {
        return $this->current?->id;
    }

    public function slug(): ?string
    {
        return $this->current?->slug;
    }

    /**
     * Build URL berbasis path kampus, contoh tenant_url('/admin') => /kampus-utama/admin.
     */
    public function url(string $path = ''): string
    {
        $slug = $this->slug();

        if ($slug === null) {
            return url($path);
        }

        return url($slug.'/'.ltrim($path, '/'));
    }

    /**
     * Membuat URL dari named route tanpa perlu menyebut param {kampus} di setiap pemanggilan.
     */
    public function route(string $name, array $parameters = [], bool $absolute = true): string
    {
        if (! array_key_exists('kampus', $parameters)) {
            $parameters['kampus'] = $this->slug();
        }

        return URL::route($name, $parameters, $absolute);
    }

    /**
     * Namespace key cache agar antar kampus tidak saling menimpa.
     */
    public function cacheKey(string $key): string
    {
        return $this->isSetup() ? "kampus.{$this->id()}.{$key}" : $key;
    }
}
