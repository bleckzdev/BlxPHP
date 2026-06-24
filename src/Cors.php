<?php
namespace BlxPHP;
class Cors
{
    public static function setCors(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE, PATCH');
            header('Access-Control-Allow-Headers: Authorization, Content-Type');

            http_response_code(200);
            exit;
        }

        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header("Access-Control-Allow-Credentials: true");
    }
}