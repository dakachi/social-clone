<?php

use Illuminate\Support\Facades\Route;
use Modules\AdminAIReport\Http\Controllers\AdminAIReportController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware(['web', 'auth'])->group(function () {
    Route::prefix('admin/ai/report')->group(function () {
        Route::get('/', [AdminAIReportController::class, 'index'])->name('admin.ai.report.index');
        Route::post('statistics', [AdminAIReportController::class, 'statistics'])->name('admin.ai.report.statistics');
        Route::post('export_pdf', [AdminAIReportController::class, 'exportPdf'])->name('admin.ai.report.export_pdf');
    });
});

