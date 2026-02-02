<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuthViewController extends Controller
{
    public function showLogin(Request $request)
    {
        if ($request->has('module')) {
            session(['intended_module' => $request->get('module')]);
        }

        return view('auth.login', [
            'intendedModule' => session('intended_module'),
        ]);
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function showResetPassword(Request $request)
    {
        return view('auth.reset-password', [
            'token' => $request->route('token'),
            'email' => $request->email
        ]);
    }
}
