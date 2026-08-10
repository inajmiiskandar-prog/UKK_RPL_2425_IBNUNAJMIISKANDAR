<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\MasterData\AreaController;
use App\Http\Controllers\MasterData\PopController;
use App\Http\Controllers\MasterData\OltController;
use App\Http\Controllers\MasterData\OdpController;
use App\Http\Controllers\MasterData\OntController;
use App\Http\Controllers\MasterData\PortPonController;
use App\Http\Controllers\MasterData\PaketController;
use App\Http\Controllers\MasterData\MaterialController;
use App\Http\Controllers\Jaringan\FabController;
use App\Http\Controllers\Jaringan\BaaController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

// ======================================================
// Auth (tidak perlu login)
// ======================================================
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LogoutController::class, 'logout'])->name('logout');

// ======================================================
// Wajib login
// ======================================================
Route::middleware('auth')->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.home');

    // Profil user yang sedang login (semua role)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Master Data - wajib role ADMIN
    Route::middleware('role:ADMIN')->prefix('masterdata')->name('masterdata.')->group(function () {
        Route::resource('area', AreaController::class);
        Route::resource('pop', PopController::class);
        Route::resource('olt', OltController::class);
        Route::resource('odp', OdpController::class);
        Route::resource('port-pon', PortPonController::class);
        Route::resource('paket', PaketController::class);
    });

    // Material & ONT - ADMIN + LOGISTIK
    Route::middleware('role:ADMIN,LOGISTIK')->prefix('masterdata')->name('masterdata.')->group(function () {
        Route::resource('material', MaterialController::class);
        Route::resource('ont', OntController::class);
    });

    // FAB - ADMIN + LEADER + SALES
    Route::middleware('role:ADMIN,LEADER,SALES')->prefix('jaringan')->name('jaringan.')->group(function () {
        Route::resource('fab', FabController::class);
    });

    // BAA - ADMIN + LEADER + TEKNISI
    Route::middleware('role:ADMIN,LEADER,TEKNISI')->prefix('jaringan')->name('jaringan.')->group(function () {
        Route::resource('baa', BaaController::class);
    });

    // User Management - ADMIN saja
    Route::middleware('role:ADMIN')->group(function () {
        Route::resource('users', UserController::class);
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
        Route::get('/settings/reset', [SettingsController::class, 'reset'])->name('settings.reset');
    });
});