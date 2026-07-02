<?php

namespace App\Http\Middleware;

use App\Modules\Core\Filament\Concerns\RequiresModuleFeature;
use App\Support\FeatureRegistry;
use App\Support\ModuleRegistry;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware ao nível do módulo activável (ModuleRegistry).
 *
 * Rotas web/API usam `module:slug` para pacotes inteiros (ex.: relatórios, cartões).
 * Páginas Filament e acções pontuais usam {@see FeatureRegistry}
 * via {@see RequiresModuleFeature} ou verificações explícitas.
 */
class EnsureModuleEnabled
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $module): Response
    {
        if (ModuleRegistry::enabled($module)) {
            return $next($request);
        }

        if ($request->is('api/*') || $request->expectsJson()) {
            return response()->json([
                'message' => ModuleRegistry::disabledMessage($module),
                'module' => $module,
            ], 403);
        }

        abort(404);
    }
}
