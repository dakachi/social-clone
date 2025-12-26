<?php

use Illuminate\Support\Facades\Route;
use Modules\AppProfile\Http\Controllers\AppProfileController;

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
    Route::group(["prefix" => "app"], function () {
        Route::group(["prefix" => "profile"], function () {
            Route::get('/', [AppProfileController::class, 'index'])->name('app.profile');
            Route::get('/{page}', [AppProfileController::class, 'index'])->name('app.profile');
            Route::get('billing/invoice/{id_secure}', [AppProfileController::class, 'showInvoice'])->name('app.profile.billing.show_invoice');
            Route::get('billing/invoice/{id_secure}/download', [AppProfileController::class, 'downloadInvoice'])->name('app.profile.billing.download_invoice');
            Route::post('update-profile', [AppProfileController::class, 'updateProfile'])->name('app.profile.update_profile');
            Route::post('change-password', [AppProfileController::class, 'changePassword'])->name('app.profile.change_password');
        });

        Route::post('plan/activate/{id_secure}', [AppProfileController::class, 'activateFreePlan'])->name('plan.activate');
    }); 
}); 