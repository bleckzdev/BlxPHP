<?php
namespace BlxPHP;
class Env
{
    private static bool $loaded = false;

    public static function loadEnv(): void
    {
        global $_ENV;
        $path = $_SERVER['DOCUMENT_ROOT'] . '/.env';

        if (!file_exists($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $_ENV[trim($key)] = trim($value);
            }
        }

        self::$loaded = true;
    }

    public static function get(string $key): ?string
    {
        if (!self::$loaded) {
            self::loadEnv();
        }

        global $_ENV;
        return $_ENV[$key] ?? null;
    }
}