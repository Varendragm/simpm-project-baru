<?php

use App\Http\Controllers\Api\BootstrapController;
use App\Http\Controllers\Api\MachineController;
use App\Http\Controllers\Api\PmScheduleController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\StationController;
use App\Http\Controllers\AppController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Halaman
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->to(auth()->check() ? '/app' : '/login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/app', [AppController::class, 'index'])->name('app.index');

    /*
    |----------------------------------------------------------------------
    | API (session + CSRF, dipanggil dari public/js/app.js)
    |----------------------------------------------------------------------
    */
    Route::prefix('api')->group(function () {
        Route::get('/bootstrap', BootstrapController::class);

        Route::post('/stations', [StationController::class, 'store']);
        Route::post('/machines', [MachineController::class, 'store']);

        Route::post('/pm-schedules', [PmScheduleController::class, 'store']);
        Route::post('/pm-schedules/{pmSchedule}/laporan', [PmScheduleController::class, 'submitLaporan']);
        Route::post('/pm-schedules/{pmSchedule}/validasi', [PmScheduleController::class, 'validasi']);

        Route::post('/profile/password', [ProfileController::class, 'updatePassword']);
    });
});
