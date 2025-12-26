<?php

use Illuminate\Support\Facades\Route;
use Modules\AdminBlogTags\Http\Controllers\AdminBlogTagsController;


Route::middleware(['web', 'auth'])->group(function () {
    Route::group(["prefix" => "admin"], function () {
        Route::group(["prefix" => "blogs/tags"], function () {
            Route::resource('/', AdminBlogTagsController::class)->names('admin.blogs.tags');
            Route::post('update', [AdminBlogTagsController::class, 'update'])->name('admin.blogs.tags.update');
            Route::post('save', [AdminBlogTagsController::class, 'save'])->name('admin.blogs.tags.save');
            Route::post('list', [AdminBlogTagsController::class, 'list'])->name('admin.blogs.tags.list');
            Route::post('destroy', [AdminBlogTagsController::class, 'destroy'])->name('admin.blogs.tags.destroy');
            Route::post('status/{any}', [AdminBlogTagsController::class, 'status'])->name('app.blogs.tags.status');
        });
    });
});
