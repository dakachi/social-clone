<?php

use Illuminate\Support\Facades\Route;
use Modules\AdminBlogCategories\Http\Controllers\AdminBlogCategoriesController;

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
    Route::group(["prefix" => "admin/blogs"], function () {
        Route::group(["prefix" => "categories"], function () {
            Route::resource('/', AdminBlogCategoriesController::class)->only(['index'])->names('admin.blogs.categories');
            Route::post('list', [AdminBlogCategoriesController::class, 'list'])->name('admin.blogs.categories.list');
            Route::post('update', [AdminBlogCategoriesController::class, 'update'])->name('app.blogs.categories.update');
            Route::post('save', [AdminBlogCategoriesController::class, 'save'])->name('app.blogs.categories.save');
            Route::post('status/{any}', [AdminBlogCategoriesController::class, 'status'])->name('app.blogs.categories.status');
            Route::post('destroy', [AdminBlogCategoriesController::class, 'destroy'])->name('app.blogs.categories.destroy');
        });
    });
});
