<?php

use Illuminate\Support\Facades\Route;
use Modules\AdminSupportCategories\Http\Controllers\AdminSupportCategoriesController;

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
    Route::group(["prefix" => "admin/support/categories"], function () {
        Route::resource('/', AdminSupportCategoriesController::class)->only(['index'])->names('admin.support.categories');
        Route::post('list', [AdminSupportCategoriesController::class, 'list'])->name('admin.support.categories.list');
        Route::post('update', [AdminSupportCategoriesController::class, 'update'])->name('admin.support.categories.update');
        Route::post('save', [AdminSupportCategoriesController::class, 'save'])->name('admin.support.categories.save');
        Route::post('status/{any}', [AdminSupportCategoriesController::class, 'status'])->name('admin.support.categories.status');
        Route::post('destroy', [AdminSupportCategoriesController::class, 'destroy'])->name('admin.support.categories.destroy');
    });
});
