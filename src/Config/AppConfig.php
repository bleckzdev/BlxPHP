<?php

namespace BlxPHP\Config;

class AppConfig {

    public function __construct(
        public array $Methods = [
            "GET",
            "POST",
            "PUT",
            "DELETE"
        ],
        public array $AllowedOrigins = [],
    ) {
    }
}
