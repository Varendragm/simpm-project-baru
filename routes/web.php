<?php

use App\Http\Controllers\Api\BootstrapController;
use App\Http\Controllers\Api\MachineController;
use App\Http\Controllers\Api\PmScheduleController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\StationController;
use App\Http\Controllers\AppController;
use App\Http\Controllers\AuthController;
use App\Http\Middleware\EnsurePmScheduleAssignment;
use App\Http\Middleware\EnsureRole;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->to(auth()->check() ? '/app' : '/login'));
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/app', [AppController::class, 'index'])->name('app.index');

    Route::prefix('api')->group(function () {
        Route::get('/bootstrap', BootstrapController::class);

        Route::middleware(EnsureRole::class . ':supervisor')->group(function () {
            Route::post('/stations', [StationController::class, 'store']);
            Route::put('/stations/{station}', [StationController::class, 'update']);
            Route::delete('/stations/{station}', [StationController::class, 'destroy']);
            Route::post('/machines', [MachineController::class, 'store']);
            Route::put('/machines/{machine}', [MachineController::class, 'update']);
            Route::delete('/machines/{machine}', [MachineController::class, 'destroy']);
            Route::post('/pm-schedules', [PmScheduleController::class, 'store'])
                ->middleware(EnsurePmScheduleAssignment::class);
            Route::post('/pm-schedules/{pmSchedule}/validasi', [PmScheduleController::class, 'validasi']);
        });

        Route::middleware(EnsureRole::class . ':teknisi')->group(function () {
            Route::post('/pm-schedules/{pmSchedule}/laporan', [PmScheduleController::class, 'submitLaporan'])
                ->middleware(EnsurePmScheduleAssignment::class);
        });

        Route::post('/profile/password', [ProfileController::class, 'updatePassword']);
    });
});
