<?php

namespace BlxPHP\Interfaces;

class RouteInterface {

    public function __construct(
        public string $path,
        public string $method,
        public $Controller,
        public string $action,
        public array $guards = []
    ) {

    }
}
