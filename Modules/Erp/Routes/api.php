<?php

use Illuminate\Support\Facades\Route;
use Modules\Erp\Http\Controllers\ErpProductController;
use Modules\Erp\Http\Controllers\SalesOrderController;
use Modules\Erp\Jobs\Webhook\ErpProductUpdate;

Route::group(["middleware" => ["api"]], function () {
    Route::group([
        "prefix"=>"admin",
        "as" => "admin.",
        "middleware" => ["admin", "language"]
    ], function () {
        Route::group([
            "prefix"=>"erp",
            "as" => "erp.",
        ], function () {
            Route::group([
                "prefix"=>"mappers",
                "as" => "mappers.",
            ], function () {
                Route::resource("attributes", ShippingAttributeMapperController::class);
                Route::get("payment/methods", [Modules\Erp\Http\Controllers\PaymentMethodMapperController::class, "getPaymentMethod"])->name('payment.methods.index');
                Route::resource("payment", PaymentMethodMapperController::class);
            });
            Route::resource("mappers", NavErpOrderMapperController::class);


            Route::group([
                "prefix" => "products",
                "as" => "products.",
            ], function () {
                Route::post("bulk/update", [ErpProductController::class, "bulkUpdate"])->name("bulk.update");
                Route::post("{id}", [ErpProductController::class, "update"])->name("update");
            });

            Route::get("webhooks/orders/{id}", [SalesOrderController::class, "initalizeWebhook"])->name("orders.webhook.initalize");
        });

    });

    Route::group([
        "middleware" => ["erp"],
        "prefix" => "erp",
        "as" => "erp",
    ], function () {
        Route::post("website/{website_id}/products", [ErpProductController::class, "store"])->name("product.store");
    });
});


