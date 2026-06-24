<?php
namespace BlxPHP;

class Responser
{
    public static function json($data, $status = 200)
    {
        header('Content-Type: application/json');
        http_response_code($status);
        print_r(json_encode($data));
        exit;
    }

    public static function success($data = [], $message = 'OK')
    {
        self::json([
            'status' => 'success',
            'desc' => $message,
            'data' => $data
        ]);
    }

    public static function error($message = 'Error interno', $status = 500)
    {
        self::json([
            'status' => 'error',
            'desc' => $message,
            'data' => []
        ], $status);
    }

    public static function badRequest($message = 'Datos inválidos')
    {
        self::error($message, 400);
    }

    public static function notFound($message = 'Recurso no encontrado')
    {
        self::error($message, 404);
    }

    public static function unauthorized($message = 'No autorizado')
    {
        self::error($message, 401);
    }

    public static function noData($message = 'No data')
    {
        self::error($message, 200);
    }

    public static function customError($status = "error", $message = 'Error interno', $statusCode = 500)
    {
        self::json([
            'status' => $status,
            'desc' => $message
        ], $statusCode);
    }

    public static function Debug($data)
    {
        self::json([
            'status' => 'debug',
            'data' => $data
        ]);
    }

    public static function csv($data, $status = 200)
    {
        header('Content-Type: text/csv');
        http_response_code($status);
        print_r($data);
        exit;
    }

    public static function image($data, $status = 200)
    {
        header('Content-Type: image/jpeg');
        header('Content-Length: ' . filesize($data));
        http_response_code($status);
        readfile($data);
        exit;
    }

}