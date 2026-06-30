<?php
namespace BlxPHP\Router;

use BlxPHP\Interfaces\RouteInterface;
use BlxPHP\Responser;

class LoadRoutes
{
    private static bool $matched = false;
    public static function load(RouteInterface $route): void
    {
        if (self::$matched || $_SERVER['REQUEST_METHOD'] !== $route->method)
            return;

        $urlParts = explode("/", $_GET['url'] ?? '');
        $routeParts = explode("/", $route->path);

        if (count($urlParts) !== count($routeParts))
            return;

        $isMatch = true;
        global $_GET;
        for ($i = 0; $i < count($urlParts); $i++) {
            if (str_contains($routeParts[$i], ':')) {
                $paramName = "url_" . str_replace(':', '', $routeParts[$i]);
                $_GET[$paramName] = $urlParts[$i];
            } elseif ($urlParts[$i] !== $routeParts[$i] && (!isset($routeParts[$i][0]) || $routeParts[$i][0] !== ':')) {
                $isMatch = false;
                break;
            }
        }

        if ($isMatch) {

            foreach ($route->guards as $guard) {
                new $guard();
            }

            self::$matched = true;
            $controller = new $route->Controller();
            $controller->{$route->action}();
        }
    }

    public static function NotFound(): bool
    {
        return !self::$matched;
    }
}