<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureModuleAccess
{
    public function handle(Request $request, Closure $next, string $module): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login', ['module' => $module]);
        }

        if (!$user->hasModuleAccess($module)) {
            abort(403, "You don't have access to this module.");
        }

        session(['current_module' => $module]);

        return $next($request);
    }
}
