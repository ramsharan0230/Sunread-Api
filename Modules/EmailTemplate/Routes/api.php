<?php

Route::group(["prefix" => "admin", "as" => "admin.", "middleware" => ["api", "admin", "language"]], function () {

    Route::group(["prefix" => "email-templates", "as" => "email-templates."], function () {

        Route::get("/groups", [\Modules\EmailTemplate\Http\Controllers\EmailTemplateController::class, "templateGroup"])->name("groups");
        Route::get("/variables", [\Modules\EmailTemplate\Http\Controllers\EmailTemplateController::class, "templateVariable"])->name("variables");
        Route::get("/{id}/content", [\Modules\EmailTemplate\Http\Controllers\EmailTemplateController::class, "getTemplateContent"])->name("content");

    });
    Route::resource("/email-templates", EmailTemplateController::class);
});
