<?php

namespace BlxPHP\Utils;

class Device {

    public function __construct() {

    }

    public static function getIP():string
    {
        $headers = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ips = explode(',', $_SERVER[$header]);
                foreach ($ips as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP)) {
                        return $ip;
                    }
                }
            }
        }
        return 'UNKNOWN';
    }

    public static function getDevice():string 
    {
        $user_agent = $_SERVER["HTTP_USER_AGENT"];
        $device = "Unknown Device";
        if (preg_match('/mobile/i', $user_agent)) {
            $device = "Mobile";
        } elseif (preg_match('/tablet/i', $user_agent)) {
            $device = "Tablet";
        } elseif (preg_match('/iPad/i', $user_agent)) {
            $device = "iPad";
        } elseif (preg_match('/iPhone/i', $user_agent)) {
            $device = "iPhone";
        } elseif (preg_match('/android/i', $user_agent)) {
            $device = "Android";
        } elseif (preg_match('/windows/i', $user_agent)) {
            $device = "Windows";
        } elseif (preg_match('/macintosh|mac os x/i', $user_agent)) {
            $device = "Mac";
        } elseif (preg_match('/linux/i', $user_agent)) {
            $device = "Linux";
        }
        return $device;
    }
}
