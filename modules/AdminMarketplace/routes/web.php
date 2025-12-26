<?php

use Illuminate\Support\Facades\Route;
use Modules\AdminMarketplace\Http\Controllers\AdminMarketplaceController;

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
    Route::prefix('admin/marketplace')->name('admin.marketplace.')->group(function () {
        Route::get('/', [AdminMarketplaceController::class, 'index'])->name('index');
        Route::get('addons', [AdminMarketplaceController::class, 'addons'])->name('addons');
        Route::get('detail/{slug}', [AdminMarketplaceController::class, 'detail'])->name('detail');
        Route::get('detail/{slug}/faqs', [AdminMarketplaceController::class, 'detail'])->name('faqs');
        Route::get('detail/{slug}/support', [AdminMarketplaceController::class, 'detail'])->name('support');
        Route::get('detail/{slug}/changelog', [AdminMarketplaceController::class, 'detail'])->name('changelog');
        Route::post('install', [AdminMarketplaceController::class, 'install'])->name('install');
        Route::post('do-install', [AdminMarketplaceController::class, 'doInstall'])->name('do_install');
        Route::post('do-install-zip', [AdminMarketplaceController::class, 'doInstallZip'])->name('do_install_zip');
        Route::post('do-update/{product_id}', [AdminMarketplaceController::class, 'doUpdate'])->name('do_update');
        Route::post('active/{id}', [AdminMarketplaceController::class, 'active'])->name('active');
        Route::post('deactive/{id}', [AdminMarketplaceController::class, 'deactive'])->name('deactive');
        Route::post('destroy/{id}', [AdminMarketplaceController::class, 'destroy'])->name('destroy');
    });
});