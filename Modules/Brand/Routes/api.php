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


Route::group(["prefix" => "admin", "middleware" => ["api","admin", "language"], "as" => "admin."], function () {
    Route::resource("brands", BrandController::class)->except(["create","edit"]);
});