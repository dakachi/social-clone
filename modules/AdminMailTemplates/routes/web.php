<?php

use Illuminate\Support\Facades\Route;
use Modules\AdminMailTemplates\Http\Controllers\AdminMailTemplatesController;

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
    Route::group(["prefix" => "admin/mail"], function () {
        Route::group(["prefix" => "templates"], function () {
            Route::resource('/', AdminMailTemplatesController::class)->only(['index'])->names('admin.mail.templates');
            Route::post('get_template', [AdminMailTemplatesController::class, 'getTemplateContent'])->name('admin.mail.templates.get_template');
            Route::post('save_template', [AdminMailTemplatesController::class, 'saveTemplateContent'])->name('admin.mail.templates.save_template');
        });

    });
});
