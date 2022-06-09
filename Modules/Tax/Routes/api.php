<?php

Route::group(["middleware" => ["api"]], function () {
    // ADMIN TAX RATE ROUTES
    Route::group(["prefix" => "admin/taxes", "as" => "admin.taxes.", "middleware" => ["admin", "language"]], function () {
        Route::resource("rates", TaxRateController::class)->except(["create", "edit"]);

        Route::group(["prefix" => "groups", "as" => "groups."], function () {
            Route::get("customers/all", [\Modules\Tax\Http\Controllers\CustomerTaxGroupController::class, "all"])->name("customers.all");
            Route::get("products/all", [\Modules\Tax\Http\Controllers\ProductTaxGroupController::class, "all"])->name("products.all");
            Route::resource("customers", CustomerTaxGroupController::class)->except(["create", "edit"]);
            Route::resource("products", ProductTaxGroupController::class)->except(["create", "edit"]);
        });

        Route::put("/rules/{tax_rule_id}/status", [\Modules\Tax\Http\Controllers\TaxRuleController::class, "updateStatus"])->name("rules.status");
        Route::resource('rules', TaxRuleController::class)->except(['create', 'edit']);
    });
});
