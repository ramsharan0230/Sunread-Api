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

Route::group(['middleware' => ['api','proxies']], function () {
    
    Route::group(['prefix' => 'admin/sales', 'as' => 'admin.sales.', 'middleware' => ['admin', 'language']], function () {
        Route::post('orders/status/update', [Modules\Sales\Http\Controllers\OrderController::class, 'orderStatus'])->name('order.status');
        
        Route::group(["prefix" => "order", "as" => "comments."], function () {
            Route::get("{order_id}/comments", [Modules\Sales\Http\Controllers\OrderCommentController::class, "index"])->name("index");
            Route::post("{order_id}/comments", [Modules\Sales\Http\Controllers\OrderCommentController::class, "store"])->name("store");
            Route::put("{order_id}/comments/{comment_id}", [Modules\Sales\Http\Controllers\OrderCommentController::class, "update"])->name("update");
            Route::get("{order_id}/comments/{comment_id}", [Modules\Sales\Http\Controllers\OrderCommentController::class, "show"])->name("show");
            Route::delete("{order_id}/comments/{comment_id}", [Modules\Sales\Http\Controllers\OrderCommentController::class, "destroy"])->name("destroy");
        });
        
        Route::resource("orders", OrderController::class)->only(["index", "show"]);

        Route::get("states", [Modules\Sales\Http\Controllers\OrderStatusController::class, "getAllOrderState"])->name("order.states");

        Route::resource("statuses", OrderStatusController::class)->only(["index", "show", "update", "store"]);
    });

    Route::group(['as' => 'public.sales.', 'prefix' => 'public'], function () {
        Route::get('shipping/payment/methods', [Modules\Sales\Http\Controllers\StoreFront\OrderController::class, 'getShippingAndPaymentMethods'])->name('shipping.payment.methods');
        Route::get('checkout/order', [Modules\Sales\Http\Controllers\StoreFront\OrderController::class, 'fetchOrderDetail'])->name('checkout.order.detail');
        Route::resource("checkout", \StoreFront\OrderController::class)->only(["store", "show"]);
    });

    Route::group(['as' => 'customer.sales.', 'prefix' => 'customers', 'middleware' => ['customer', 'language']], function () {
        Route::resource("order", \StoreFront\OrderController::class)->only(["index", "show"]);
    });

});
