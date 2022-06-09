<?php

namespace Modules\Core\Traits;

trait ResponseMessage
{
    public string $model_name;
    public array $response_message;
    public array $lang = [
        "fetch-list-success" => "response.fetch-list-success",
        "fetch-success" => "response.fetch-success",
        "create-success" => "response.create-success",
        "update-success" => "response.update-success",
        "delete-success" => "response.deleted-success",
        "delete-error" => "response.deleted-error",
        "last-delete-error" => "response.last-delete-error",
        "not-found" => "response.not-found",
        "login-error" => "users.users.login-error",
        "login-success" => "users.users.login-success",
        "logout-success" => "users.users.logout-success",
        "token-generation-problem" => "users.token.token-generation-problem",
        "password-reset-success" => "users.reset-password.password-reset-success",
        "status-updated" => "response.status-updated",
        "reindex-success" => "response.reindex-success",
        "bulk-reindex-success" => "response.bulk-reindex-success",
    ];


    public function lang(string $key, ?array $parameters = null, string $module = "core::app"): string
    {
        $parameters = $parameters ?? ["name" => $this->model_name];
        $translation_key = $this->lang[$key] ?? $key;

        return __("{$module}.{$translation_key}", $parameters);
    }

}
