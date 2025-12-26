<?php

use Illuminate\Support\Facades\Route;
use Modules\AdminUsers\Http\Controllers\AdminUsersController;

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
    Route::group(["prefix" => "admin"], function () {
        Route::group(["prefix" => "users"], function () {
            Route::resource('/', AdminUsersController::class)->names('admin.users');
            Route::post('list', [AdminUsersController::class, 'list'])->name('admin.users.list');
            Route::get('export', [AdminUsersController::class, 'export'])->name('admin.users.export');
            Route::get('create', [AdminUsersController::class, 'create'])->name('admin.users.create');
            Route::get('edit/{any}', [AdminUsersController::class, 'edit'])->name('admin.users.edit');
            Route::post('save', [AdminUsersController::class, 'save'])->name('admin.users.save');
            Route::post('change_password', [AdminUsersController::class, 'change_password'])->name('admin.users.change_password');
            Route::post('update_plan', [AdminUsersController::class, 'update_plan'])->name('admin.users.update_plan');
            Route::post('update_info', [AdminUsersController::class, 'update_info'])->name('admin.users.update_info');
            Route::post('destroy', [AdminUsersController::class, 'destroy'])->name('admin.users.destroy');
            Route::post('status/{any}', [AdminUsersController::class, 'status'])->name('admin.users.status');

            Route::get('search', [AdminUsersController::class, 'get_search_users'])->name('admin.users.search');
        });
    });
});