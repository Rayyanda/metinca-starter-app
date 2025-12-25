<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\AuthViewController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\BatchController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\POInternalController;
use App\Http\Controllers\BatchOperationController;
use App\Http\Controllers\DivisionController;
use App\Http\Controllers\OperationController;
use App\Http\Controllers\MachineController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\QualityCheckController;

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


Route::middleware(['auth'])->group(function(){

    //dashboard
    Route::get('/dashboard',[DashboardController::class,'dashboard'])->name('dashboard');

    // PO Internal
    Route::resource('po-internals', POInternalController::class);
    Route::post('/po-internals/{id}/confirm', [POInternalController::class, 'confirm'])->name('po-internals.confirm');

    //Batches
    Route::resource('batches',BatchController::class);

    Route::resource('machines',MachineController::class);
    // Downtime Management
    Route::post('/machines/{id}/report-downtime', [MachineController::class, 'reportDowntime'])->name('machines.report-downtime');
    Route::post('/downtimes/{id}/resolve', [MachineController::class, 'resolveDowntime'])->name('machines.resolve-downtime');
    Route::get('/machines/{id}/downtime-history', [MachineController::class, 'downtimeHistory'])->name('machines.downtime-history');
    
    // Additional Actions
    Route::post('/machines/{id}/toggle-active', [MachineController::class, 'toggleActive'])->name('machines.toggle-active');
    Route::post('/machines/bulk-update-status', [MachineController::class, 'bulkUpdateStatus'])->name('machines.bulk-update-status');
    Route::get('/machines/export', [MachineController::class, 'export'])->name('machines.export');
    
    // API Endpoints
    Route::prefix('api/machines')->name('api.machines.')->group(function () {
        Route::get('/available-for-operation/{operationId}', [MachineController::class, 'getAvailableForOperation'])->name('available-for-operation');
        Route::get('/{id}/statistics', [MachineController::class, 'getStatistics'])->name('statistics');
    });

    Route::resource('operations',OperationController::class);

    Route::resource('divisions',DivisionController::class);

    Route::resource('roles',RoleController::class);

    Route::resource('permissions',PermissionController::class);

    Route::resource('users',UserController::class);

    Route::prefix('operation')->name('operations.')->group(function(){

        Route::post('/{id}/add-machine', [OperationController::class, 'addMachine'])->name('add-machine');
            Route::delete('/{operationId}/remove-machine/{machineId}', [OperationController::class, 'removeMachine'])->name('remove-machine');
            Route::put('/{operationId}/update-machine/{machineId}', [OperationController::class, 'updateMachine'])->name('update-machine');
            
            // Additional actions
            Route::post('/{id}/toggle-active', [OperationController::class, 'toggleActive'])->name('toggle-active');
            Route::post('/{id}/duplicate', [OperationController::class, 'duplicate'])->name('duplicate');
            Route::post('/update-sequence', [OperationController::class, 'updateSequence'])->name('update-sequence');
    });



    Route::get('/my-operations', [BatchOperationController::class, 'myOperations'])->name('operations.my');
    Route::get('/operations/{id}/start', [BatchOperationController::class, 'showStartForm'])->name('operations.start');
    Route::post('/operations/{id}/start', [BatchOperationController::class, 'start'])->name('operations.start.post');
    Route::get('/operations/{id}/complete', [BatchOperationController::class, 'showCompleteForm'])->name('operations.complete');
    Route::post('/operations/{id}/complete', [BatchOperationController::class, 'complete'])->name('operations.complete.post');
    Route::post('/operations/{id}/pause', [BatchOperationController::class, 'pause'])->name('operations.pause');
    Route::post('/operations/{id}/resume', [BatchOperationController::class, 'resume'])->name('operations.resume');

    //machining process
    Route::prefix('machining')->name('machining.')->group(function(){

        //monitoring
        Route::prefix('monitoring')->name('monitoring.')->group(function(){

            Route::get('/',function(){
                return view('machining.monitoring.index');
            })->name('index');



        });

    });

    //wax room process
    //Route::resource('wax-rooms',App\Http\Controllers\Process\WaxRoomController::class);
    Route::get('wax-rooms',[App\Http\Controllers\Process\WaxRoomController::class,'qualityView'])->name('wax-rooms.quality-view');

    //for production tracking module

    //quality check every division
    Route::get('/track/{division}/quality', [App\Http\Controllers\ProductionTrackingController::class, 'trackQuality'])->name('quality.track');
    Route::get('/track/{division}/quality/pending', [App\Http\Controllers\ProductionTrackingController::class, 'qualityPending'])->name('qc.pending');
    Route::get('/track/{division}/quality/pending', [App\Http\Controllers\ProductionTrackingController::class, 'qualityPending'])->name('qc.pending');

    // Quality Check (Dynamic per Division)
    Route::prefix('qc')->name('qc.')->group(function () {
        // Pending QC
        Route::get('/pending', [QualityCheckController::class, 'pending'])->name('pending');
        
        // Check Form
        Route::get('/check/{batchOperationId}', [QualityCheckController::class, 'showCheckForm'])->name('check-form');
        Route::post('/check/{batchOperationId}', [QualityCheckController::class, 'submit'])->name('submit');
        
        // History
        Route::get('/history', [QualityCheckController::class, 'history'])->name('history');
        
        // Detail
        Route::get('/{id}', [QualityCheckController::class, 'show'])->name('show');
        
        // Approve conditional (Supervisor only)
        Route::post('/{id}/approve-conditional', [QualityCheckController::class, 'approveConditional'])->name('approve-conditional');
        
        // Export
        Route::get('/export/csv', [QualityCheckController::class, 'export'])->name('export');
    });

    // API Endpoints
    Route::prefix('api/qc')->name('api.qc.')->group(function () {
        Route::get('/statistics', [QualityCheckController::class, 'getStatistics'])->name('statistics');
    });

});

