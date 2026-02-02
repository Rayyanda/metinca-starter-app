<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    public function store(LoginRequest $request): JsonResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = Auth::user();
        $intendedModule = session()->pull('intended_module');
        $redirectUrl = route('dashboard');

        if ($intendedModule) {
            if ($user->hasModuleAccess($intendedModule)) {
                session(['current_module' => $intendedModule]);
                $redirectUrl = route("{$intendedModule}.dashboard");
            }
        }

        return response()->json([
            'message' => 'Login successful',
            'redirect' => $redirectUrl,
        ]);
    }

    public function destroy(Request $request): Response
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return response()->noContent();
    }
}
