<?php

use Illuminate\Support\Facades\Route;
use Modules\AdminAffiliateCommissions\Http\Controllers\AdminAffiliateCommissionsController;

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
    Route::prefix('admin/affiliate-commissions')->group(function () {
        Route::get('/', [AdminAffiliateCommissionsController::class, 'index'])->name('admin.affiliatecommissions.index');
        Route::post('list', [AdminAffiliateCommissionsController::class, 'list'])->name('admin.affiliatecommissions.list');
        Route::post('update', [AdminAffiliateCommissionsController::class, 'update'])->name('app.affiliatecommissions.update');
        Route::post('status/{any}', [AdminAffiliateCommissionsController::class, 'status'])->name('app.affiliatecommissions.status');
        Route::post('destroy', [AdminAffiliateCommissionsController::class, 'destroy'])->name('app.affiliatecommissions.destroy');
        Route::get('statistics', [AdminAffiliateCommissionsController::class, 'statistics']);
    });
});