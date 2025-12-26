<?php

use Illuminate\Support\Facades\Route;
use Modules\AdminDashboard\Http\Controllers\AdminDashboardController;

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
    Route::group(["prefix" => "admin"], function () {
        Route::resource('/dashboard', AdminDashboardController::class)->only(['index'])->names('admin.dashboard');
        Route::post('dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
        Route::post('statistics', [AdminDashboardController::class, 'statistics'])->name('admin.dashboard.statistics');
    });
});
 