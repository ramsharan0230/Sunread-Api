<?php

Route::group(["middleware" => ["api"]], function() {
    // ADMIN PRODUCT ROUTES
    Route::group(["prefix" => "admin/catalog", "as" => "admin.catalog.", "middleware" => ["admin", "language"]], function() {

        Route::get('products/reindex', [\Modules\Product\Http\Controllers\ReindexController::class, "reIndex"])->name('products.reindex');

        // Catalog Product Routes
        Route::get("product/configurations", [\Modules\Product\Http\Controllers\ProductController::class, "configurations"])->name("products.configurations");
        Route::put("/products/{product_id}/status", [\Modules\Product\Http\Controllers\ProductController::class, "updateStatus"])->name("products.status");
        Route::resource("products", ProductController::class)->except(["create", "edit"]);
        Route::get("product/attributes/{id}", [\Modules\Product\Http\Controllers\ProductController::class, "product_attributes"])->name("products.attributes.show");
        Route::get("product/configurable/{id}", [\Modules\Product\Http\Controllers\ProductController::class, "variants"])->name("products.configurable.show");
        Route::resource("configurable-products", ProductConfigurableController::class)->except(["create", "edit", "index", "show"]);
        //Route::get("products/cache", [\Modules\Product\Http\Controllers\ProductController::class, "cache"])->name("products.cache");

        // Product Images Routes
        Route::group(['prefix' => 'product', 'as' => 'products.'], function() {
            Route::put('image/{id}/change-main-image', [\Modules\Product\Http\Controllers\ProductImageController::class,"changeMainImage"])->name("image.change_main_image");
            Route::resource('image', ProductImageController::class)->only(['store', 'destroy']);
        });

        Route::put("/features/{feature_id}/status", [\Modules\Product\Http\Controllers\FeatureController::class, "updateStatus"])->name("features.status");
        Route::resource("features", FeatureController::class)->except(["create", "edit"]);

        // Product Images Routes
        Route::get('products/{category_id}/category', [\Modules\Product\Http\Controllers\ProductController::class,"categoryWiseProducts"])->name("products.categoryWiseProducts");
    });

    Route::group(['prefix'=>'public', 'as' => 'public.'], function () {
        Route::get('catalog/category/{category_slug?}/navigation/layered', [\Modules\Product\Http\Controllers\StoreFront\ProductController::class, "filter"])->name("products.filter")->where('category_slug', '.*');
        Route::get('catalog/category/{category_slug?}/products', [\Modules\Product\Http\Controllers\StoreFront\ProductController::class, "index"])->name("products.index")->where('category_slug', '.*');
        Route::get('catalog/product/{parent_id}/configurable/variant/{id}', [\Modules\Product\Http\Controllers\StoreFront\ProductController::class, "variantShow"])->name("products.configurable.variants");
        Route::get('catalog/category/{category_slug?}', [\Modules\Product\Http\Controllers\StoreFront\ProductController::class, "category"])->name("products.category")->where('category_slug', '.*');
        Route::get('catalog/product/{url_key}', [\Modules\Product\Http\Controllers\StoreFront\ProductController::class, "show"])->name("products.show");
        Route::get('catalog/search', [\Modules\Product\Http\Controllers\StoreFront\ProductController::class, "search"])->name("products.search");
        Route::get('test/catalog/search', [\Modules\Product\Http\Controllers\StoreFront\ProductController::class, "testSearch"])->name("products.testSearch");
        Route::get('test/category/{category_slug?}/products', [\Modules\Product\Http\Controllers\StoreFront\ProductController::class, "testIndex"])->name("products.testIndex")->where('category_slug', '.*');
    });
});
