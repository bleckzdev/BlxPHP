<?php
namespace BlxPHP;
use BlxPHP\Responser;
use ErrorException;

class ErrorHandler
{
    public static function init()
    {
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
        register_shutdown_function([self::class, 'handleShutdown']);
    }

    public static function handleError($errno, $errstr, $errfile, $errline)
    {
        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    }

    public static function handleException($exception)
    {
        Responser::error("Error del servidor: " . $exception->getMessage(), 500);
    }

    public static function handleShutdown()
    {
        $error = error_get_last();
        if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            http_response_code(500);
            echo json_encode(["status" => "error", "desc" => "Error crítico: " . $error['message']]);
        }
    }
}