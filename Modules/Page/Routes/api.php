<?php

use Modules\Page\Http\Controllers\PageAvailabilityController;

Route::group(['middleware' => ['api']], function () {
    //ADMIN PAGES ROUTES
    Route::group(['prefix'=>'admin', 'as' => 'admin.', 'middleware' => ['admin', 'language']], function () {
        Route::get('pages/components', [\Modules\Page\Http\Controllers\PageAttributeController::class, "index"])->name("components.index");
        Route::get('pages/components/{component_slug}', [\Modules\Page\Http\Controllers\PageAttributeController::class, "show"])->name("components.show");
        Route::put("pages/{page_id}/status", [\Modules\Page\Http\Controllers\PageController::class, "updateStatus"])->name("pages.status");
        Route::resource('pages', PageController::class)->except(['create', 'edit']);
    });
});

Route::group(['prefix'=>'public', 'as' => 'public.'], function () {
    Route::get('pages', [\Modules\Page\Http\Controllers\StoreFront\PageController::class, "index"])->name("pages.index");
    Route::get('pages/{page_slug}', [\Modules\Page\Http\Controllers\StoreFront\PageController::class, "show"])->name("pages.show");
});
