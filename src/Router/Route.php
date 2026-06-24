<?php
namespace BlxPHP\Router;

use BlxPHP\Interfaces\RouteInterface;

class Route
{

    public static function Get(string $route, $controller, $action, array $guards = []): RouteInterface
    {
        return new RouteInterface(
            $route,
            "GET",
            $controller,
            $action,
            $guards
        );
    }

    public static function Post(string $route, $controller, $action, array $guards = []): RouteInterface
    {
        return new RouteInterface(
            $route,
            "POST",
            $controller,
            $action,
            $guards
        );
    }

    public static function Put(string $route, $controller, $action, array $guards = []): RouteInterface
    {
        return new RouteInterface(  
            $route,
            "PUT",
            $controller,
            $action,
            $guards
        );
    }

    public static function Patch(string $route, $controller, $action, array $guards = []): RouteInterface
    {
        return new RouteInterface(
            $route,
            "PATCH",
            $controller,
            $action,
            $guards
        );
    }

    public static function Delete(string $route, $controller, $action, array $guards = []): RouteInterface
    {
        return new RouteInterface(
            $route,
            "DELETE",
            $controller,
            $action,
            $guards
        );
    }

    public static function NotFound(string $route, $controller, $action, array $guards = []): RouteInterface
    {
        return new RouteInterface(
            $route,
            "NOT_FOUND",
            $controller,
            $action,
            $guards
        );
    }
}