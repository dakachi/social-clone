<?php

use Illuminate\Support\Facades\Route;
use Modules\AppGroups\Http\Controllers\AppGroupsController;

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

Route::group(["prefix" => "app"], function () {
    Route::group(["prefix" => "groups"], function () {
        Route::resource('/', AppGroupsController::class)->names('app.groups');
        Route::post('update', [AppGroupsController::class, 'update'])->name('app.groups.update');
        Route::post('save', [AppGroupsController::class, 'save'])->name('app.groups.save');
        Route::post('list', [AppGroupsController::class, 'list'])->name('app.groups.list');
        Route::post('destroy', [AppGroupsController::class, 'destroy'])->name('app.groups.destroy');
    });
});