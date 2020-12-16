<?php declare(strict_types = 1);

/**
 * PHP version 7.4
 *
 * @category  PHP
 * @package   Madsoft\Talkbot
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */

namespace Madsoft\Talkbot;

use Madsoft\Library\App\WebApp;
use Madsoft\Library\Invoker;
use Madsoft\Library\RouteCache;

require_once __DIR__ . '/../vendor/autoload.php';

$routesExt = [];
require 'routes.app.php';
 
$invoker = new Invoker();
$routeCache = $invoker->getInstance(RouteCache::class);
$routes = $routeCache->loadRoutes(
    array_merge(
        [
            __DIR__ . '/Library/routes/web.app.routes.php',
            __DIR__ . '/Library/routes/web.my-contents.routes.php',
            __DIR__ . '/Library/routes/web.testing.routes.php',
            __DIR__ . '/Library/routes/web.navbar.routes.php',
        ],
        $routesExt
    ),
    'web'
);

$app = new WebApp($invoker, $routeCache);
$app->setRoutes($routes);
$app->run();
