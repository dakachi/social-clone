<?php

use Illuminate\Support\Facades\Route;
use Modules\AppTeamJoined\Http\Controllers\AppTeamJoinedController;

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
    Route::group(["prefix" => "app/joined-teams"], function () {
        Route::get('index', [AppTeamJoinedController::class, 'index'])->name('app.teams.joined');
        Route::post('list', [AppTeamJoinedController::class, 'list'])->name('app.teams.joined.list');
        Route::post('leave', [AppTeamJoinedController::class, 'leaveTeam'])->name('app.teams.joined.leave_team');
        Route::post('open', [AppTeamJoinedController::class, 'openTeam'])->name('app.teams.joined.open_team');
        Route::post('destroy', [AppTeamJoinedController::class, 'destroy'])->name('app.teams.joined.destroy');

    });
});

Route::get('/join-team', [AppTeamJoinedController::class, 'joinTeam'])->name('app.teams.join');