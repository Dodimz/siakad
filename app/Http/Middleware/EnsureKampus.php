<?php

namespace App\Http\Middleware;

use App\Models\Kampus;
use App\Support\TenantManager;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class EnsureKampus
{
    /**
     * Resolve kampus dari segmen pertama URL dan bind ke konteks tenant.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $slug = $request->route('kampus');

        if (! $slug) {
            abort(404);
        }

        $kampus = Kampus::where('slug', $slug)->first();

        if (! $kampus) {
            abort(404);
        }

        if ($kampus->status === Kampus::STATUS_SUSPENDED) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Kampus ini sedang nonaktif.'], 403);
            }

            return redirect('/')->withErrors([
                'kampus' => 'Kampus ini sedang nonaktif.',
            ]);
        }

        $user = $request->user();

        $userInKampus = $user
            && ! $user->isSuperAdmin()
            && $user->kampus_id
            && $user->kampus_id !== $kampus->id;

        if ($userInKampus) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Anda tidak terdaftar di kampus ini.'], 403);
            }

            abort(403, 'Anda tidak terdaftar di kampus ini.');
        }

        app(TenantManager::class)->set($kampus);

        URL::defaults(['kampus' => $kampus->slug]);

        // Jangan biarkan segmen {kampus} ikut diteruskan ke method controller
        // sebagai argumen posisional (akan merusak route model binding).
        $request->route()?->forgetParameter('kampus');

        return $next($request);
    }
}
