<?php

Route::group(['middleware' => ['api']], function () {
    //ADMIN COUNTRY ROUTES
    Route::group(['prefix'=>'admin', 'as' => 'admin.', 'middleware' => ['admin', 'language']], function () {

        Route::get('/country/regions', [Modules\Country\Http\Controllers\RegionController::class, "countryWiseRegion"])->name("country.regions.list");
        Route::get('/regions/cities', [Modules\Country\Http\Controllers\CityController::class, "regionWiseCity"])->name("region.cities.list");
        Route::get('/channel/{channel_id}/country', [ \Modules\Country\Http\Controllers\CountryController::class, "channelCountry" ]);
        Route::resource('country', CountryController::class)->only(['index', 'show']);

        Route::resource('regions', RegionController::class)->only(['index', 'show']);
        Route::resource('cities', CityController::class)->only(['index', 'show']);
    });

//    PUBLIC COUNTRY ROUTES
    Route::group(['prefix'=> 'public', 'as' => 'public.'], function () {

        Route::get('/channel/countries', [ \Modules\Country\Http\Controllers\StoreFront\CountryController::class, "channelCountries" ]);

        Route::group(['prefix'=> 'countries'], function () {

            Route::get('/', [ \Modules\Country\Http\Controllers\StoreFront\CountryController::class, "index" ]);
            Route::get('/{country_id}/regions', [ \Modules\Country\Http\Controllers\StoreFront\RegionController::class, "index" ]);
            Route::get('/{country_id}/regions/{region_id}/cities', [ \Modules\Country\Http\Controllers\StoreFront\CityController::class, "index" ]);
        });
    });
});
