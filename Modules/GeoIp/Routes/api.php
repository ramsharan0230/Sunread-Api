<?php

use Illuminate\Http\Request;
use Modules\GeoIp\Facades\GeoIp;

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

Route::get("test-ip", function () {
    return response()->json([
        "ip" => request()->ip(),
        "client_ip" => GeoIp::requestIp(),
        "location" => GeoIp::locate(request()->ip())?->toArray(),
    ]);
})->middleware("proxies");
