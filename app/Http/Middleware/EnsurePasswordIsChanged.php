<?php

namespace App\Http\Middleware;

use App\Filament\Pages\ChangeRequiredPassword;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordIsChanged
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user?->must_change_password) {
            return $next($request);
        }

        $routeName = $request->route()?->getName() ?? '';

        if (
            str_contains($routeName, 'change-required-password')
            || str_contains($routeName, 'logout')
            || str_contains($routeName, 'login')
        ) {
            return $next($request);
        }

        return redirect()->to(ChangeRequiredPassword::getUrl());
    }
}
