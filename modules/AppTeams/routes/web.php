<?php

use Illuminate\Support\Facades\Route;
use Modules\AppTeams\Http\Controllers\AppTeamsController;

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

// Route::group([], function () {
//     Route::resource('appteams', AppTeamsController::class)->names('appteams');
// });

Route::middleware(['web', 'auth'])->group(function () {
    Route::group(["prefix" => "app/teams"], function () {
        Route::resource('', AppTeamsController::class)->names('app.teams');
        Route::get('set-team-name', [AppTeamsController::class, 'setTeamName'])->name('app.teams.set_team_name');
        Route::post('save-team-name', [AppTeamsController::class, 'saveTeamName'])->name('app.teams.save_team_name');
        Route::get('edit/{any}', [AppTeamsController::class, 'update'])->name('app.teams.edit');
        Route::post('list', [AppTeamsController::class, 'list'])->name('app.teams.list');
        Route::post('update', [AppTeamsController::class, 'update'])->name('app.teams.update');
        Route::post('invite', [AppTeamsController::class, 'invite'])->name('app.teams.invite');
        Route::post('send-invite', [AppTeamsController::class, 'sendInvite'])->name('app.teams.send_invite');
        Route::post('resend-invite', [AppTeamsController::class, 'resendInvite'])->name('app.teams.resend_invite');
        Route::post('save', [AppTeamsController::class, 'save'])->name('app.teams.save');
        Route::post('status/{any}', [AppTeamsController::class, 'status'])->name('app.teams.status');
        Route::post('destroy', [AppTeamsController::class, 'destroy'])->name('app.teams.destroy');
    });
});