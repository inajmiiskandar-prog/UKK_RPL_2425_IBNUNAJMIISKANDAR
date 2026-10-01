<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
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
use App\Http\Controllers\NotificationController;

// KPI Controllers
use App\Http\Controllers\Kpi\KpiSoftSkillController;
use App\Http\Controllers\Kpi\KpiHardSkillController;
use App\Http\Controllers\Kpi\KpiPeriodController;
use App\Http\Controllers\Kpi\KpiAssessmentController;
use App\Http\Controllers\Kpi\KpiEmployeeController;
use App\Http\Controllers\Kpi\KpiReportController;
use Illuminate\Support\Facades\Route;

// ======================================================
// Publik
// ======================================================
Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : view('auth.login');
})->name('home');

// ======================================================
// Wajib login
// ======================================================
Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::post('/notifications/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');

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
        Route::post('material/{material}/add-stock', [MaterialController::class, 'addStock'])->name('material.addStock');
        Route::resource('ont', OntController::class);
    });

    // FAB - ADMIN + LEADER + SALES
    Route::middleware('role:ADMIN,LEADER,SALES')->prefix('jaringan')->name('jaringan.')->group(function () {
        Route::resource('fab', FabController::class)->except(['destroy']);
    });

    // Penghapusan FAB hanya boleh dilakukan ADMIN
    Route::middleware('role:ADMIN')->prefix('jaringan')->name('jaringan.')->group(function () {
        Route::delete('fab/{fab}', [FabController::class, 'destroy'])->name('fab.destroy');
    });

    // BAA - ADMIN + LEADER + TEKNISI
    Route::middleware('role:ADMIN,LEADER,TEKNISI')->prefix('jaringan')->name('jaringan.')->group(function () {
        Route::resource('baa', BaaController::class)->except(['destroy']);
    });

    // Penghapusan BAA hanya boleh dilakukan ADMIN
    Route::middleware('role:ADMIN')->prefix('jaringan')->name('jaringan.')->group(function () {
        Route::delete('baa/{baa}', [BaaController::class, 'destroy'])->name('baa.destroy');
    });

    // User Management - ADMIN saja
    Route::middleware('role:ADMIN')->group(function () {
        Route::resource('users', UserController::class);
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
        Route::get('/settings/reset', [SettingsController::class, 'reset'])->name('settings.reset');
    });

    // ======================================================
    // KPI Routes - Semua role yang login bisa akses
    // ======================================================

    // Master Data KPI: Soft Skill (ADMIN only), Hard Skill (ADMIN & LEADER bisa create/update, ADMIN only untuk destroy)
    Route::middleware('role:ADMIN')->prefix('kpi')->name('kpi.')->group(function () {
        Route::resource('soft-skill', KpiSoftSkillController::class);
    });
    // Hard Skill: ADMIN & LEADER bisa create/edit, ADMIN only untuk destroy
    Route::middleware('role:ADMIN,LEADER')->prefix('kpi')->name('kpi.')->group(function () {
        Route::resource('hard-skill', KpiHardSkillController::class)->only(['index', 'create', 'store', 'edit', 'update']);
    });
    Route::middleware('role:ADMIN')->prefix('kpi')->name('kpi.')->group(function () {
        Route::resource('hard-skill', KpiHardSkillController::class)->only(['destroy']);
    });

    // KPI Dashboard - semua role login bisa akses
    Route::prefix('kpi')->name('kpi.')->group(function () {
        Route::get('dashboard', [App\Http\Controllers\Kpi\KpiDashboardController::class, 'index'])->name('dashboard');
    });

    // Data Karyawan - ADMIN bisa edit, semua bisa lihat
    Route::prefix('kpi')->name('kpi.')->group(function () {
        // Export harus sebelum resource route agar tidak tertangkap wildcard
        Route::middleware('role:ADMIN,LEADER')->group(function () {
            Route::get('employee/export', [KpiEmployeeController::class, 'export'])->name('employee.export');
        });
        Route::resource('employee', KpiEmployeeController::class)->only(['index', 'show', 'create', 'store']);
        Route::middleware('role:ADMIN')->group(function () {
            Route::resource('employee', KpiEmployeeController::class)->only(['edit', 'update']);
            Route::post('employee/{employee}/reset-password', [KpiEmployeeController::class, 'resetPassword'])->name('employee.reset-password');
            Route::post('employee/{employee}/toggle-status', [KpiEmployeeController::class, 'toggleStatus'])->name('employee.toggle-status');
        });
    });

    // Periode KPI - ADMIN only
    Route::middleware('role:ADMIN')->prefix('kpi')->name('kpi.')->group(function () {
        Route::resource('period', KpiPeriodController::class);
        Route::post('period/{period}/generate', [KpiPeriodController::class, 'generateAssessments'])->name('period.generate');
        Route::post('period/{period}/activate', [KpiPeriodController::class, 'activate'])->name('period.activate');
    });

    // Penilaian KPI - Semua role
    Route::prefix('kpi')->name('kpi.')->group(function () {
        // Halaman utama & histori
        Route::get('assessment', [KpiAssessmentController::class, 'index'])->name('assessment.index');
        Route::get('assessment/history', [KpiAssessmentController::class, 'history'])->name('assessment.history');

        // Wizard Penilaian KPI (4 step)
        Route::get('assessment/create', [KpiAssessmentController::class, 'wizardStep1'])->name('assessment.create');
        Route::post('assessment/wizard/step1', [KpiAssessmentController::class, 'wizardPostStep1'])->name('assessment.wizard.step1');
        Route::get('assessment/wizard/step2', [KpiAssessmentController::class, 'wizardStep2'])->name('assessment.wizard.step2');
        Route::post('assessment/wizard/step2', [KpiAssessmentController::class, 'wizardPostStep2'])->name('assessment.wizard.step2.store');
        Route::get('assessment/wizard/step3', [KpiAssessmentController::class, 'wizardStep3'])->name('assessment.wizard.step3');
        Route::post('assessment/wizard/step3', [KpiAssessmentController::class, 'wizardPostStep3'])->name('assessment.wizard.step3.store');
        Route::get('assessment/wizard/step4', [KpiAssessmentController::class, 'wizardStep4'])->name('assessment.wizard.step4');
        Route::post('assessment/wizard/submit', [KpiAssessmentController::class, 'wizardSubmit'])->name('assessment.wizard.submit');

        // Self Assessment
        Route::get('assessment/self', [KpiAssessmentController::class, 'selfAssessment'])->name('assessment.self');
        Route::post('assessment/{assessment}/self', [KpiAssessmentController::class, 'storeSelfAssessment'])->name('assessment.self.store');

        // Penilaian Atasan
        Route::get('assessment/{assessment}/atasan', [KpiAssessmentController::class, 'atasanAssessment'])->name('assessment.atasan');
        Route::post('assessment/{assessment}/atasan', [KpiAssessmentController::class, 'storeAtasanAssessment'])->name('assessment.atasan.store');

        // Rekap (Admin only)
        Route::middleware('role:ADMIN')->group(function () {
            Route::get('assessment/recap', [KpiAssessmentController::class, 'recap'])->name('assessment.recap');
        });

        // Detail Assessment
        Route::get('assessment/{assessment}', [KpiAssessmentController::class, 'show'])->name('assessment.show');
    });

    // Export Laporan - ADMIN only
    Route::middleware('role:ADMIN')->prefix('kpi')->name('kpi.')->group(function () {
        Route::get('report', [KpiReportController::class, 'index'])->name('report.index');
        Route::get('report/preview', [KpiReportController::class, 'preview'])->name('report.preview');
        Route::get('report/exportCsv/{period}', [KpiReportController::class, 'exportCsv'])->name('report.exportCsv');
        Route::get('report/exportExcel/{period}', [KpiReportController::class, 'exportExcel'])->name('report.exportExcel');
    });
});