<?php

use Illuminate\Support\Facades\Route;
use Modules\AdminCache\Http\Controllers\AdminCacheController;

Route::middleware(['web', 'auth'])->group(function () {
    Route::prefix("admin/settings")->group(function () {
        // Trang hiển thị danh sách cache tools
        Route::get('cache', [AdminCacheController::class, 'index'])->name('admin.settings.cache.index');

        // API clear cache
        Route::post('cache/clear', [AdminCacheController::class, 'clear'])->name('admin.settings.cache.clear');
    });
});