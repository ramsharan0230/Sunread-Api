<?php

namespace Modules\Cart\Services;

class UserAuthCheckService
{
    public function validateUser(object $request): mixed
    {
        if ($request->hasHeader('authorization')) {
            return auth("customer")->userOrFail();
        } else {
            return false;
        }
    }
}
