<?php

use Illuminate\Support\Facades\Route;
use Modules\AdminCaptcha\Http\Controllers\AdminCaptchaController;

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
    Route::group(["prefix" => "admin/captcha-configuration"], function () {
        Route::resource('/', AdminCaptchaController::class)->only(['index'])->names('admin.captcha.configuration');
    });
});