<?php

use App\Modules\Repair\Controllers\DamageReportController;
use App\Modules\Repair\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

Route::prefix('reports')->name('reports.')->group(function () {
    Route::get('/', [DamageReportController::class, 'index'])
        ->name('index')
        ->middleware('permission:repair.view');

    Route::get('/create', [DamageReportController::class, 'create'])
        ->name('create')
        ->middleware('permission:repair.create');

    Route::post('/', [DamageReportController::class, 'store'])
        ->name('store')
        ->middleware('permission:repair.create');

    Route::get('/export', [DamageReportController::class, 'export'])
        ->name('export')
        ->middleware('permission:repair.export');

    Route::get('/{damageReport}', [DamageReportController::class, 'show'])
        ->name('show')
        ->middleware('permission:repair.view');

    Route::post('/{damageReport}/status', [DamageReportController::class, 'updateStatus'])
        ->name('status')
        ->middleware('permission:repair.update-status');
});
