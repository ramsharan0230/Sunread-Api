<?php

namespace Modules\User\Services;

class  Bouncer
{
    /**
     * Checks if admin is allowed or not for certain action
     *
     * @param  String $permission
     * @return bool
     */
    public function hasPermission($permission)
    {
        if (auth()->guard('admin')->check() && auth()->guard('admin')->user()->role->permission_type == 'all') {
            return true;
        } else {
            if (! auth()->guard('admin')->check() || ! auth()->guard('admin')->user()->hasPermission($permission))
                return false;
        }

        return true;
    }

    /**
     * Checks if admin is  allowed or not for certain action
     *
     * @param $route
     * @return bool
     */
    public static function allow($route)
    {
        if (!auth()->guard('admin')->check()) return false;

        $acl = config('acl');
        $key_for_route = array_search($route, array_column($acl, 'route'),true);
        if($key_for_route === false) return  false;

        $permission = $acl[$key_for_route]['key'];
        $permission_group = "";

        foreach( explode('.', $permission) as $key ) {
            $permission_group .= "{$key}.";
            $check_permission = ($permission_group == "{$permission}.") ? $permission : "{$permission_group}all";

            if ( auth()->guard('admin')->user()->hasPermission($check_permission) ) return true;
        }

        return false;
    }
}
