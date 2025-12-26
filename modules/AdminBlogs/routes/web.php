<?php

use Illuminate\Support\Facades\Route;
use Modules\AdminBlogs\Http\Controllers\AdminBlogsController;

Route::middleware(['web', 'auth'])->group(function () {
    Route::group(["prefix" => "admin/blogs"], function () {
        Route::resource('', AdminBlogsController::class)->names('admin.blogs');
        Route::post('list', [AdminBlogsController::class, 'list'])->name('admin.blogs.list');
        Route::get('update', [AdminBlogsController::class, 'update'])->name('admin.blogs.update');
        Route::get('edit/{id}', [AdminBlogsController::class, 'update'])->name('admin.blogs.edit');
        Route::post('save', [AdminBlogsController::class, 'save'])->name('admin.blogs.save');
        Route::post('status/{any}', [AdminBlogsController::class, 'status'])->name('admin.blogs.status');
        Route::post('destroy', [AdminBlogsController::class, 'destroy'])->name('admin.blogs.destroy');
    });
});
