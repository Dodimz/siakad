<?php

namespace Tests;

use App\Models\Kampus;
use App\Support\TenantManager;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\URL;

abstract class TestCase extends BaseTestCase
{
    protected string $kampusSlug = 'kampus-utama';

    protected function setUp(): void
    {
        parent::setUp();

        $kampus = Kampus::firstOrCreate(
            ['slug' => $this->kampusSlug],
            [
                'nama' => 'Kampus Test',
                'status' => 'active',
            ]
        );

        app(TenantManager::class)->set($kampus);
        URL::defaults(['kampus' => $this->kampusSlug]);
    }

    /**
     * Prefix path dengan kampus slug untuk pengujian route tenant.
     */
    protected function tenantPath(string $path): string
    {
        return '/'.$this->kampusSlug.'/'.ltrim($path, '/');
    }
}
