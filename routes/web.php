<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\AuthViewController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/',function(){
    if(Auth::check()){
        return redirect()->route('dashboard');
    }
    return redirect()->route('home.main');
});

require __DIR__.'/auth.php';

// ============================================
// GUEST ROUTES (belum login)
// ============================================
Route::middleware('guest')->group(function () {
    
    // GET - Show Forms (Custom Views)
    Route::get('login', [AuthViewController::class, 'showLogin'])
        ->name('login');
    
    Route::get('register', [AuthViewController::class, 'showRegister'])
        ->name('register');
    
    Route::get('forgot-password', [AuthViewController::class, 'showForgotPassword'])
        ->name('password.request');
    
    Route::get('reset-password/{token}', [AuthViewController::class, 'showResetPassword'])
        ->name('password.reset');

    // POST - Handle Logic (Breeze Controllers)
    Route::post('register', [RegisteredUserController::class, 'store'])
        ->name('register.store');
    
    Route::post('login', [AuthenticatedSessionController::class, 'store'])
        ->name('login.store');
    
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');
    
    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.update');

    Route::prefix('home')->group(function(){

        Route::get('/',function(){
            return view('home.main');
        })->name('home.main');

        Route::get('/products',function(){
            return view('home.products');
        })->name('home.products');

        Route::get('/divisions',function(){
            return view('home.divisions');
        })->name('home.divisions');

        Route::get('/facilities',function(){
            return view('home.facilities');
        })->name('home.facilities');

        Route::get('/gallery',function(){
            return view('home.galleries');
        })->name('home.gallery');

    });
});

// ============================================
// Auth ROUTES (sudah login)
// ============================================


Route::middleware(['auth','verified'])->group(function(){

    //dashboard
    Route::get('/dashboard',[DashboardController::class,'dashboard'])->name('dashboard');

    //machining process
    Route::prefix('machining')->name('machining.')->group(function(){

        Route::get('dashboard',[App\Http\Controllers\Machining\DashboardController::class,'index'])->name('dashboard.index');

    });

    Route::resource('roles',\App\Http\Controllers\RoleController::class);

});

