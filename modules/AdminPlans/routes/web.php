<?php

use Illuminate\Support\Facades\Route;
use Modules\AdminPlans\Http\Controllers\AdminPlansController;

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
        Route::group(["prefix" => "plans"], function () {
            Route::resource('', AdminPlansController::class)->names('admin.plans');
            Route::get('create', [AdminPlansController::class, 'update'])->name('admin.plan.create');
            Route::get('edit/{any}', [AdminPlansController::class, 'update'])->name('admin.plan.edit');
            Route::post('list', [AdminPlansController::class, 'list'])->name('admin.plan.list');
            Route::post('save', [AdminPlansController::class, 'save'])->name('admin.plan.save');
            Route::post('status/{any}', [AdminPlansController::class, 'status'])->name('admin.plan.status');
            Route::post('destroy', [AdminPlansController::class, 'destroy'])->name('admin.plan.destroy');
        });
    });
});