<?php

use Illuminate\Http\Request;

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

Route::post("public/product/alert/stock", [\Modules\ProductStockAlert\Http\Controllers\ProductStockAlertController::class, "createProductStockAlert"])->name('create.product.stock.alert');
