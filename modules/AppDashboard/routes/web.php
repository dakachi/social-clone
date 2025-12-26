<?php

use Illuminate\Support\Facades\Route;
use Modules\AppDashboard\Http\Controllers\AppDashboardController;
use Modules\AppDashboard\Livewire\Dashboard;

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
        Route::resource('/dashboard', AppDashboardController::class)->only(['index'])->names('app.dashboard');
        Route::post('dashboard', [AppDashboardController::class, 'index'])->name('app.dashboard');
        Route::post('/dashboard/statistics', [AppDashboardController::class, 'statistics'])->name('app.dashboard.statistics');
    });
});
