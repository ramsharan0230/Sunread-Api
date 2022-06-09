<?php

use Modules\User\Http\Controllers\AccountController;
use Modules\User\Http\Controllers\SessionController;
use Modules\User\Http\Controllers\ResetPasswordController;
use Modules\User\Http\Controllers\ForgotPasswordController;
use Modules\User\Http\Controllers\UserInvitationController;

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

//USER MODULE ROUTES
Route::group(["middleware" => ["api"], "prefix" => "admin", "as" => "admin."], function() {
    // Session Routes
    Route::post("/login", [SessionController::class, "login"])->name("session.login");
    Route::post("/forget-password", [ForgotPasswordController::class, "store"])->name("forget-password.store");
    Route::post("/reset-password", [ResetPasswordController::class, "store"])->name("reset-password.store");
    Route::get("/reset-password/{token}", [ResetPasswordController::class, "create"])->name("reset-password.create");

    Route::group(["middleware" => ["jwt.verify"]], function() {
        // Session Routes
        Route::get("/logout", [SessionController::class, "logout"])->name("session.logout");

        // Roles Routes
        Route::get("permissions", [\Modules\User\Http\Controllers\RoleController::class, "fetchPermission"]);
        Route::resource("roles", RoleController::class);

        // User Routes
        Route::put("/users/{user_id}/status", [\Modules\User\Http\Controllers\UserController::class, "updateStatus"])->name("users.status");
        Route::put("/users/{user_id}/resend-invitation", [\Modules\User\Http\Controllers\UserController::class, "resendInvitation"])->name("users.resend-invitation");
        Route::resource("users", UserController::class)->except(['create', 'edit']);


        // Account Routes
        Route::group(["prefix" => "account", "as" => "account."], function() {
            Route::get("/", [AccountController::class, "show"])->name("show");
            Route::put("/", [AccountController::class, "update"])->name("update");
            Route::put("password", [AccountController::class, "password"])->name("password");
            Route::post("image", [AccountController::class, "uploadProfileImage"])->name("image.update");
            Route::delete("image", [AccountController::class, "deleteProfileImage"])->name("image.delete");
        });
    });

    Route::get("/invitation-info/{invitation_token}", [UserInvitationController::class, "getInvitationToken"])->name("invitation-info");
    Route::post("/accept-invitation", [UserInvitationController::class, "acceptInvitation"])->name("accept-invitation");
});
