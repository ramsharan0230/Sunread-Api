<?php

use Illuminate\Http\Request;

Route::group(["middleware" => ["api"]], function () {
    // ADMIN COUPON ROUTES
    Route::group(["prefix" => "admin", "as" => "admin.", "middleware" => ["api", "admin", "language"]], function () {

        Route::group(['prefix' => 'coupons', 'as' => 'coupons.'], function () {
            Route::post("/{coupon_id}/allow-coupon", [\Modules\Coupon\Http\Controllers\AllowCouponController::class, "allowCoupon"])->name('allow_coupon');
            Route::delete("/delete-allow-coupon", [\Modules\Coupon\Http\Controllers\AllowCouponController::class, "deleteAllowCoupon"])->name('delete_allow_coupon');
            Route::get("/model-list", [\Modules\Coupon\Http\Controllers\CouponController::class, "modelList"])->name('model_list');
            Route::put("/{coupon_id}/status", [\Modules\Coupon\Http\Controllers\CouponController::class, "updateStatus"])->name('status');
        });

        Route::resource("/coupons", CouponController::class);
    });
});
