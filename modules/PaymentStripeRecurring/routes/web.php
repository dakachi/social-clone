<?php

use Illuminate\Support\Facades\Route;
use Modules\PaymentStripeRecurring\Http\Controllers\PaymentStripeRecurringController;

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

        Route::post('payment/configuration/stripe_recurring', function (Request $request) {
            return response()->json([
                "status" => 1,
                "data" => view('paymentstriperecurring::configuration')->render()
            ]);
        });

    });
});