<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::group(['middleware' => ['api','proxies'], 'prefix' => 'public/checkout/payment/adyen', 'as' => 'adyen.'], function () {
    Route::post("order/status", [\Modules\PaymentAdyen\Http\Controllers\PaymentAdyenController::class, "updateOrderStatus"])->name("update.order.status");
    Route::post("payment/details", [\Modules\PaymentAdyen\Http\Controllers\PaymentAdyenController::class, "paymentDetails"])->name("payment.details");
    Route::post("notification/webhook", [\Modules\PaymentAdyen\Http\Controllers\PaymentAdyenController::class, "notificationWebhook"])->name("notification.webhook");
});