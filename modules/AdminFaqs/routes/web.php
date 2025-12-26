<?php

use Illuminate\Support\Facades\Route;
use Modules\AdminFaqs\Http\Controllers\AdminFaqsController;

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
;

Route::middleware(['web', 'auth'])->group(function () {
    Route::group(["prefix" => "admin/faqs"], function () {
        Route::resource('/', AdminFaqsController::class)->only(['index'])->names('admin.faqs');
        Route::post('list', [AdminFaqsController::class, 'list'])->name('admin.faqs.list');
        Route::get('create', [AdminFaqsController::class, 'update'])->name('app.faqs.create');
        Route::get('edit/{id}', [AdminFaqsController::class, 'update'])->name('app.faqs.edit');
        Route::post('save', [AdminFaqsController::class, 'save'])->name('app.faqs.save');
        Route::post('status/{any}', [AdminFaqsController::class, 'status'])->name('app.faqs.status');
        Route::post('destroy', [AdminFaqsController::class, 'destroy'])->name('app.faqs.destroy');
    });
});
