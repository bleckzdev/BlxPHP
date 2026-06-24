<?php
namespace BlxPHP;

class Request
{
    public static function get()
    {
        global $_GET;
        return $_GET;
    }

    public static function post()
    {
        global $_POST;
        return $_POST;
    }

    public static function files()
    {
        global $_FILES;
        return $_FILES;
    }

    public static function json()
    {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        return $data;
    }
}