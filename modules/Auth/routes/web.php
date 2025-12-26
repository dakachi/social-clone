<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Http\Controllers\AuthController;
use App\Http\Middleware\Themes;

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

Route::group(['middleware' => 'web'], function () {
    Route::group(["prefix" => "auth"], function () {
        Route::group(['middleware' => ['guest']], function() {
            Route::get('signup', [AuthController::class, 'signup'])->name("signup");
            Route::get('login', [AuthController::class, 'login'])->name("login");
            Route::get('forgot-password', [AuthController::class, 'forgotPassword'])->name("forgot_password");
            Route::get('recovery-password', [AuthController::class, 'recoveryPassword'])->name("password.reset");
            Route::get('resend-activation', [AuthController::class, 'resendActivation'])->name("resend_activation");
            Route::get('activation', [AuthController::class, 'activation'])->name("activation");

            Route::get('login/facebook', [AuthController::class, 'redirectFacebook']);
            Route::get('login/x', [AuthController::class, 'redirectX']);
            Route::get('login/google', [AuthController::class, 'redirectGoogle']);
            Route::get('login/facebook/callback', [AuthController::class, 'callbackFacebook']);
            Route::get('login/google/callback', [AuthController::class, 'callbackGoogle']);
            Route::get('login/x/callback', [AuthController::class, 'callbackX']);

            Route::post('do_signup', [AuthController::class, 'doSignup'])->name("auth.do-signup");
            Route::post('do_login', [AuthController::class, 'doLogin'])->name("auth.do-login"); 
            Route::post('do_forgot_password', [AuthController::class, 'doForgotPassword'])->name("auth.do_forgot_password");
            Route::post('do_recovery_password', [AuthController::class, 'doRecoveryPassword'])->name("auth.do_recovery_password");
        });

        Route::get('login-as-user', [AuthController::class, 'loginAsUser']);
        Route::get('login-as-admin', [AuthController::class, 'loginAsAdmin']);
        Route::get('logout', [AuthController::class, 'logout']);
        Route::post('sidebar-state', [AuthController::class, 'saveSidebarState']);

        Route::get('view-as-user/{any}', [AuthController::class, 'viewAsUser'])->name("auth.view_as_user");
        Route::get('leave-impersonate', [AuthController::class, 'leaveImpersonate'])->name("auth.leave_impersonate");
    });
});

Route::middleware(['web', 'auth'])->group(function () {
    Route::group(["prefix" => "admin/settings"], function () {
        Route::get('auth', [ AuthController::class, 'settings' ])->name('admin.settings.auth');
    });
});