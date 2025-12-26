<?php

use Illuminate\Support\Facades\Route;
use Modules\AdminAIConfiguration\Http\Controllers\AdminAIConfigurationController;

Route::middleware(['web', 'auth'])->prefix('admin')->group(function () {
    Route::resource('ai-configuration', AdminAIConfigurationController::class)
        ->only(['index'])
        ->names('admin.ai-configuration');

    Route::post('ai-configuration/import-all', [AdminAIConfigurationController::class, 'importAll'])
        ->name('admin.ai-configuration.import-all');

    Route::post('ai-configuration/import-json', [AdminAIConfigurationController::class, 'importJson'])
    ->name('admin.ai-configuration.import-json');
});