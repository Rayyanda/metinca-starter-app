<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
        $modules = config('modules.modules', []);

        // Get user's accessible modules with their config
        $accessibleModules = [];
        foreach ($modules as $key => $module) {
            if ($module['enabled'] && $user->hasModuleAccess($key)) {
                $accessibleModules[$key] = $module;
            }
        }

        return view('dashboard', [
            'user' => $user,
            'accessibleModules' => $accessibleModules,
        ]);
    }
}
