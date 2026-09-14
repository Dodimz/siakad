<?php

use App\Support\TenantManager;

if (! function_exists('tenant_manager')) {
    function tenant_manager(): TenantManager
    {
        return app(TenantManager::class);
    }
}

if (! function_exists('tenant_url')) {
    function tenant_url(string $path = ''): string
    {
        return app(TenantManager::class)->url($path);
    }
}

if (! function_exists('tenant_route')) {
    function tenant_route(string $name, array $parameters = [], bool $absolute = true): string
    {
        return app(TenantManager::class)->route($name, $parameters, $absolute);
    }
}

if (! function_exists('tenant_cache_key')) {
    function tenant_cache_key(string $key): string
    {
        return app(TenantManager::class)->cacheKey($key);
    }
}
