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
    // ADMIN STOREFRONT ROUTES
    Route::group(["prefix" => "admin", "middleware" => ["admin", "language"], "as" => "admin."], function () {
        Route::put('navigation-menus/{id}/status', [\Modules\NavigationMenu\Http\Controllers\NavigationMenuController::class, 'updateStatus'])->name('navigation-menus.status');
        Route::resource('navigation-menus', NavigationMenuController::class)->except(["create","edit"]);

        Route::group(["prefix" => "navigation-menu/", "as" => "navigation-menu."], function () {
            Route::put('{navigation_menu_id}/items/{navigation_menu_item_id}/status', [\Modules\NavigationMenu\Http\Controllers\NavigationMenuItemController::class, 'updateStatus'])->name('items.status');
            Route::put('{navigation_menu_id}/items/{navigation_menu_item_id}/position', [\Modules\NavigationMenu\Http\Controllers\NavigationMenuItemController::class, 'updatePosition'])->name('items.position');
            Route::resource('{navigation_menu_id}/items', NavigationMenuItemController::class)->except(["create","edit"]);
        });

        Route::get('navigation-menu/attributes', [\Modules\NavigationMenu\Http\Controllers\NavigationMenuItemController::class, "attributes"])->name("navigation-menu.items.attributes");
        Route::get('navigation-menu/locations', [\Modules\NavigationMenu\Http\Controllers\NavigationMenuItemController::class, "locations"])->name("navigation-menu.items.attributes");
    });
});

Route::group(['prefix'=>'public', 'as' => 'public.'], function () {
    Route::get('/navigation-menus', [\Modules\NavigationMenu\Http\Controllers\StoreFront\NavigationMenuController::class, "index"])->name("navigation.menus.index");
    Route::get('/navigation-menus/{slug}', [\Modules\NavigationMenu\Http\Controllers\StoreFront\NavigationMenuController::class, "show"])->name("navigation.menus.show");
});

