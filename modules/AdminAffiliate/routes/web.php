<?php

use Illuminate\Support\Facades\Route;
use Modules\AdminAffiliate\Http\Controllers\AdminAffiliateController;

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
    Route::prefix('admin/affiliate/report')->group(function () {
        Route::get('/', [AdminAffiliateController::class, 'index'])->name('admin.affiliate.index');
        Route::post('list', [AdminAffiliateController::class, 'list'])->name('admin.affiliate.list');
        Route::post('update', [AdminAffiliateController::class, 'update'])->name('admin.affiliate.update');
        Route::post('status/{any}', [AdminAffiliateController::class, 'status'])->name('admin.affiliate.status');
        Route::post('destroy', [AdminAffiliateController::class, 'destroy'])->name('admin.affiliate.destroy');
        Route::post('statistics', [AdminAffiliateController::class, 'statistics'])->name('admin.affiliate.statistics');
        Route::post('export_pdf', [AdminAffiliateController::class, 'exportPdf'])->name('admin.affiliate.export_pdf');
    });
});

