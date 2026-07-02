<?php

namespace App\Modules\Auth\Http\Middleware;

use App\Support\WebauthnSettings;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasskeysEnabled
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! WebauthnSettings::enabled()) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'message' => 'As passkeys não estão activas neste clube.',
                ], 403);
            }

            abort(404);
        }

        return $next($request);
    }
}
