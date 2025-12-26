<?php

use Illuminate\Support\Facades\Route;
use Modules\AdminAffiliateWithdrawal\Http\Controllers\AdminAffiliateWithdrawalController;

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
    Route::group(["prefix" => "admin/affiliate-withdrawal"], function () {
        Route::get('/', [AdminAffiliateWithdrawalController::class, 'index'])->name('admin.affiliatewithdrawal.index');
        Route::post('list', [AdminAffiliateWithdrawalController::class, 'list'])->name('admin.affiliatewithdrawal.list');
        Route::post('update', [AdminAffiliateWithdrawalController::class, 'update'])->name('app.affiliatewithdrawal.update');
        Route::post('save-note', [AdminAffiliateWithdrawalController::class, 'updateNote'])->name('app.affiliatewithdrawal.updateNote');
        Route::post('status/{any}', [AdminAffiliateWithdrawalController::class, 'status'])->name('app.affiliatewithdrawal.status');
        Route::post('destroy', [AdminAffiliateWithdrawalController::class, 'destroy'])->name('app.affiliatewithdrawal.destroy');
    });
});