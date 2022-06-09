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

Route::group(['middleware' => ['api']], function () {
    Route::group(["prefix" => "admin/catalog/inventory", "middleware" => ["admin", "language"], "as" => "admin.catalog."], function () {        
        Route::get("items/{product_id}", [Modules\Inventory\Http\Controllers\CatalogInventoryItemController::class, "index"])->name("inventoryItem.show");
    });
});