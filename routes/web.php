<?php

use App\Http\Controllers\AcademicClassController;
use App\Http\Controllers\CashTransactionController;
use App\Http\Controllers\DailyAttendanceController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SantriController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\SppInvoiceController;
use App\Http\Controllers\ViolationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('santri', SantriController::class)
        ->middleware('role:kepala_madrasah|admin_tu|wali_kelas');

    Route::resource('daily-attendances', DailyAttendanceController::class)
        ->middleware('role:kepala_madrasah|admin_tu|wali_kelas|guru');

    Route::get('/spp-invoices/period-status', [SppInvoiceController::class, 'periodStatusIndex'])
        ->name('spp-invoices.period-status.index')
        ->middleware('role:kepala_madrasah|admin_tu');

    Route::put('/spp-invoices/period-status', [SppInvoiceController::class, 'periodStatusUpdate'])
        ->name('spp-invoices.period-status.update')
        ->middleware('role:kepala_madrasah|admin_tu');

    Route::resource('spp-invoices', SppInvoiceController::class)
        ->middleware('role:kepala_madrasah|admin_tu');

    Route::post('/spp-invoices/bulk', [SppInvoiceController::class, 'storeBulk'])
        ->name('spp-invoices.bulk.store')
        ->middleware('role:kepala_madrasah|admin_tu');

    Route::post('/spp-invoices/{sppInvoice}/payments', [PaymentController::class, 'store'])
        ->name('spp-invoices.payments.store')
        ->middleware('role:kepala_madrasah|admin_tu');

    Route::resource('cash-transactions', CashTransactionController::class)
        ->middleware('role:kepala_madrasah|admin_tu');

    Route::resource('academic-classes', AcademicClassController::class)
        ->middleware('role:kepala_madrasah|admin_tu|wali_kelas');

    Route::resource('subjects', SubjectController::class)
        ->middleware('role:kepala_madrasah|admin_tu|wali_kelas');

    Route::resource('grades', GradeController::class)
        ->middleware('role:kepala_madrasah|admin_tu|wali_kelas|guru');

    Route::resource('violations', ViolationController::class)
        ->middleware('role:kepala_madrasah|admin_tu|wali_kelas|guru');

    Route::get('/reports/finance', [ReportController::class, 'finance'])
        ->name('reports.finance')
        ->middleware('role:kepala_madrasah|admin_tu');

    Route::get('/reports/attendance', [ReportController::class, 'attendance'])
        ->name('reports.attendance')
        ->middleware('role:kepala_madrasah|admin_tu|wali_kelas|guru');

    Route::get('/reports/semester', [ReportController::class, 'semester'])
        ->name('reports.semester')
        ->middleware('role:kepala_madrasah|admin_tu|wali_kelas|guru');

    Route::get('/imports/santri', [ImportController::class, 'santriForm'])
        ->name('imports.santri.form')
        ->middleware('role:kepala_madrasah|admin_tu');

    Route::post('/imports/santri', [ImportController::class, 'santriStore'])
        ->name('imports.santri.store')
        ->middleware('role:kepala_madrasah|admin_tu');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
