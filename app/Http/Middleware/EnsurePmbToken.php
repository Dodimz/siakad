<?php

namespace App\Http\Middleware;

use App\Models\Kampus;
use App\Support\TenantManager;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class EnsurePmbToken
{
    /**
     * Autentikasi API PMB via token per kampus (Authorization: Bearer <token>).
     * Menemukan kampus dari token lalu mengisi konteks tenant.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $this->tokenFromRequest($request);

        if (! $token) {
            return response()->json(['message' => 'Token tidak valid.'], 401);
        }

        $kampus = Kampus::whereNotNull('pmb_token')->where('pmb_token', $token)->first();

        if (! $kampus) {
            return response()->json(['message' => 'Token tidak valid.'], 401);
        }

        app(TenantManager::class)->set($kampus);
        URL::defaults(['kampus' => $kampus->slug]);

        $request->attributes->set('pmb_kampus', $kampus);

        return $next($request);
    }

    protected function tokenFromRequest(Request $request): ?string
    {
        $header = $request->header('Authorization', '');

        if (str_starts_with($header, 'Bearer ')) {
            return trim(substr($header, 7));
        }

        return $request->header('X-PMB-Token') ?: null;
    }
}
