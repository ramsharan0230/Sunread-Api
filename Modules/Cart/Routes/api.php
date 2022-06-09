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

Route::group(['as' => 'cart.', 'prefix' => 'public/checkout/cart', 'middleware' => ['api','proxies']], function () {
    
    // CART ROUTES
    Route::post("/", [\Modules\Cart\Http\Controllers\CartController::class, "addOrUpdateCart"])->name('add.update');
    Route::delete("/", [\Modules\Cart\Http\Controllers\CartController::class, "deleteProductFromCart"])->name('delete.product.from.cart');
    Route::get("/", [\Modules\Cart\Http\Controllers\CartController::class, "getAllProductFromCart"])->name('products.from.cart');
    Route::post("/merge", [\Modules\Cart\Http\Controllers\CartController::class, "mergeCart"])->name('merge.cart');

});