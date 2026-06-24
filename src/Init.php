<?php

namespace BlxPHP;

use BlxPHP\Config\AppConfig;
use BlxPHP\Router\LoadRoutes;

class Init {

    public function __construct() {
        header('x-powered-by: BLX-Framework-PHP');
    }

    public static function Bootstrap(array $router = [], AppConfig $config = new AppConfig()) {
        ErrorHandler::init();
        Cors::setCors();
        if(!empty($router)) {
            foreach($router as $route) {
                LoadRoutes::load($route);
            }

            if(LoadRoutes::NotFound()) {
                Responser::notFound();
            }
        }
    }
}
