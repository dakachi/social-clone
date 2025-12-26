<?php

use Illuminate\Support\Facades\Route;
use Modules\AdminCoupons\Http\Controllers\AdminCouponsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        Route::resource('coupons', AdminCouponsController::class)->names('admin.coupons');
        Route::group(["prefix" => "coupons"], function () {
            Route::resource('/', AdminCouponsController::class)->names('admin.coupons');
            Route::post('list', [AdminCouponsController::class, 'list'])->name('admin.coupons.list');
            Route::get('create', [AdminCouponsController::class, 'update'])->name('admin.coupons.create');
            Route::get('edit/{any}', [AdminCouponsController::class, 'update'])->name('admin.coupons.edit');
            Route::post('save', [AdminCouponsController::class, 'save'])->name('admin.coupons.save');
            Route::post('status/{any}', [AdminCouponsController::class, 'status'])->name('admin.coupons.status');
            Route::post('destroy', [AdminCouponsController::class, 'destroy'])->name('admin.coupons.destroy');
        });
    });
});

Route::middleware(['web', 'auth'])->group(function () {
    Route::group(["prefix" => "app"], function () {
        Route::group(["prefix" => "coupons"], function () {
            Route::post('apply', function (Request $request) {
                
                session()->forget('coupon');

                $coupon = DB::table("coupons")->where("code", $request->code)->where("status", 1)->first();

                if(empty($coupon))
                    return response()->json([
                        "status" => 0,
                        "message" => __("The coupon code you entered does not exist. Please check and try again.")
                    ]);

                if($coupon->start_date >= time())
                    return response()->json([
                        "status" => 0,
                        "message" => sprintf( __("The coupon becomes active on %s."), datetime_show( $coupon->start_date ))
                    ]);

                if($coupon->end_date != -1 && $coupon->end_date <= time())
                    return response()->json([
                        "status" => 0,
                        "message" => __("The coupon you entered has expired. Please try another one or contact support for help.")
                    ]);

                if($coupon->usage_limit != -1 && $coupon->usage_limit <= $coupon->usage_count)
                    return response()->json([
                        "status" => 0,
                        "message" => __("This coupon has reached its usage limit and can no longer be used.")
                    ]);

                session([ "coupon" => $coupon->id ]);

                return response()->json([
                    "status" => 1,
                    "redirect" => ""
                ]);

            })->name('app.coupons.apply');
        });
    });
});
