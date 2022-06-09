<?php

use Illuminate\Http\Request;
use Modules\PaymentKlarna\Http\Controllers\PaymentKlarnaController;

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

Route::group(["middleware" => ["api", "proxies"], "prefix" => "public/checkout/payment/klarna", "as" => "klarna."], function () {
    Route::get("confirmation/{klarna_order_id}", [PaymentKlarnaController::class, "confirm"])->name("confirm");
    Route::post("push", [PaymentKlarnaController::class, "push"])->name("push");
});
