<?php

Route::group(['middleware' => ['api']], function () {
    //ADMIN SIZE CHART ROUTES
    Route::group(['prefix'=>'admin', 'as' => 'admin.', 'middleware' => ['admin', 'language']], function () {
        Route::resource('sizecharts', SizeChartController::class)->except(['create', 'edit']);
    });
});

Route::group(['prefix'=>'public', 'as' => 'public.'], function () {
    Route::get('sizecharts', [\Modules\SizeChart\Http\Controllers\StoreFront\SizeChartController::class, "index"])->name("sizecharts.index");
    Route::get('sizecharts/{slug}', [\Modules\SizeChart\Http\Controllers\StoreFront\SizeChartController::class, "show"])->name("sizecharts.show");
});