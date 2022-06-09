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

Route::group(['middleware' => ['api']], function () {
    //ADMIN ATTRIBUTE ROUTES
    Route::group(['prefix' => 'admin/attribute', 'as' => 'admin.attribute.', 'middleware' => ['admin', 'language']], function () {
        // Catalog Family Routes
        Route::put("/sets/{set_id}/status", [\Modules\Attribute\Http\Controllers\AttributeSetController::class, "updateStatus"])->name('sets.status');
        Route::resource('sets', AttributeSetController::class)->except(['create', 'edit']);
        Route::get("sets/{set_id}/unassigned-attributes", [\Modules\Attribute\Http\Controllers\AttributeSetController::class, "unassignedAttributes"])->name('sets.unassigned.attributes');

        Route::get("sets/product/{set_id}", [\Modules\Attribute\Http\Controllers\AttributeSetController::class, "attributes"])->name("sets.attribute.format");
        Route::get("all/sets", [\Modules\Attribute\Http\Controllers\AttributeSetController::class, "listAttributeSets"])->name("sets.list");

        
        // Catalog Attribute Group Routes
        Route::resource('groups', AttributeGroupController::class)->except(['create', 'edit']);

        // Attributes Routes
        Route::get('attributes/types', [\Modules\Attribute\Http\Controllers\AttributeController::class, 'types'])->name('attributes.types');
        Route::delete('attributes/bulk', [\Modules\Attribute\Http\Controllers\AttributeController::class, 'bulkDelete'])->name('attributes.bulk-delete');
        Route::resource('attributes', AttributeController::class)->except(['create', 'edit']);
    });
});
