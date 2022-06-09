<?php

namespace Modules\GeoIp\Traits;

trait HasClientIp {

    public function getClientIp(): ?string
    {
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $clientIP = $_SERVER['HTTP_CLIENT_IP'];
        }
         elseif (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            $clientIP = $_SERVER['HTTP_CF_CONNECTING_IP']; 
        }
         elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $clientIP = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
         elseif (isset($_SERVER['HTTP_X_FORWARDED'])) {
            $clientIP = $_SERVER['HTTP_X_FORWARDED'];
        }
         elseif (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
            $clientIP = $_SERVER['HTTP_FORWARDED_FOR'];
        }
         elseif (isset($_SERVER['HTTP_FORWARDED'])) {
            $clientIP = $_SERVER['HTTP_FORWARDED'];
        }
         elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $clientIP = $_SERVER['REMOTE_ADDR'];
        }
        return $clientIP;
    }

    public function clientIp(): ?string
    {
        $ipAddress = '';
        if (! empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ipAddress = $_SERVER['HTTP_CLIENT_IP'];
        }
        elseif (! empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipAddressList = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            foreach ($ipAddressList as $ip) {
                if (! empty($ip)) {
                    $ipAddress = $ip;
                    break;
                }
            }
        }
        elseif (! empty($_SERVER['HTTP_X_FORWARDED'])) {
            $ipAddress = $_SERVER['HTTP_X_FORWARDED'];
        }
        elseif (! empty($_SERVER['HTTP_X_CLUSTER_CLIENT_IP'])) {
            $ipAddress = $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
        }
        elseif (! empty($_SERVER['HTTP_FORWARDED_FOR'])) {
            $ipAddress = $_SERVER['HTTP_FORWARDED_FOR'];
        }
        elseif (! empty($_SERVER['HTTP_FORWARDED'])) {
            $ipAddress = $_SERVER['HTTP_FORWARDED'];
        }
        elseif (! empty($_SERVER['REMOTE_ADDR'])) {
            $ipAddress = $_SERVER['REMOTE_ADDR'];
        }
        return $ipAddress;
    }

    public function requestIp(): ?string
    {
        return request()->ip();
    }

}
