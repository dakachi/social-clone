<?php

use Illuminate\Support\Facades\Route;
use Modules\AdminCrons\Http\Controllers\AdminCronsController;

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

Route::group(["prefix" => "admin"], function () {
    Route::resource('crons', AdminCronsController::class)->names('admin.crons');
});


Route::middleware(['web', 'auth'])->group(function () {
    Route::group(["prefix" => "admin"], function () {
        Route::group(["prefix" => "crons"], function () {
            Route::resource('/', AdminCronsController::class)->only(['index'])->names('admin.crons');
            Route::post('change', [AdminCronsController::class, 'change'])->name('admin.crons.change');
        });
    });
});
