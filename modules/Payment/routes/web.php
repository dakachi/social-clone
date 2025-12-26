<?php

use Illuminate\Support\Facades\Route;
use Modules\Payment\Http\Controllers\PaymentController;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;

Route::middleware(['web', 'auth'])->group(function () {
    Route::prefix('payment')->group(function () {
        Route::get('/', [PaymentController::class, 'index'])->name('payment.index');
        Route::get('/{plan}', [PaymentController::class, 'index'])->name('payment.index');
        Route::get('/checkout/{gateway}', [PaymentController::class, 'checkout'])->name('payment.checkout');
        Route::get('/refund/{gateway}', [PaymentController::class, 'refund'])->name('payment.refund');
        Route::get('/success/{gateway}', [PaymentController::class, 'success'])->name('payment.success');
        Route::get('/cancel/{gateway}', [PaymentController::class, 'cancel'])->name('payment.cancel');
        Route::post('/cancel_Subscription', [PaymentController::class, 'cancelSubscription'])->name('payment.cancel_subscription');
        Route::post('/manual-payment', [PaymentController::class, 'manualPayment'])->name('payment.manual_payment');
    });
});

Route::middleware(['web'])->group(function () {
    Route::post('/payment/webhook/{gateway}', [PaymentController::class, 'webhook'])->name('payment.webhook');
    Route::post('/payment/success/{gateway}', [PaymentController::class, 'success'])->withoutMiddleware([VerifyCsrfToken::class])->name('payment.success');
    Route::post('/payment/cancel/{gateway}', [PaymentController::class, 'cancel'])->withoutMiddleware([VerifyCsrfToken::class])->name('payment.cancel');
});