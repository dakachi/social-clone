<?php

use Illuminate\Support\Facades\Route;
use Modules\AdminBroadcast\Http\Controllers\AdminBroadcastController;

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
        Route::group(["prefix" => "settings"], function () {
            Route::get('broadcast', [AdminBroadcastController::class, 'settings'])->name('admin.broadcast.settings');
        });
    });
});


Route::get('/pusher/config', function () {
    return response()->json([
        'key'     => get_option('pusher_app_key'),
        'cluster' => get_option('pusher_cluster', 'mt1'),
        'host'    => get_option('pusher_host'),
        'port'    => get_option('pusher_port', 443),
        'scheme'  => get_option('pusher_scheme', 'https'),
    ]);
})->middleware('auth');