<?php

namespace App\Http\Middleware;

use App\Models\Kampus;
use App\Support\TenantManager;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class SetDefaultKampus
{
    /**
     * Set konteks tenant ke kampus "rumah" superadmin agar URL tenant
     * tetap dapat dibangkitkan dari halaman pusat /central.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        $kampus = null;

        if ($user && $user->kampus_id) {
            $kampus = Kampus::where('id', $user->kampus_id)->first();
        }

        $kampus ??= Kampus::active()->orderBy('id')->first();

        if ($kampus) {
            app(TenantManager::class)->set($kampus);
            URL::defaults(['kampus' => $kampus->slug]);
        }

        return $next($request);
    }
}
