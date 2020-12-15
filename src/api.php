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

use Madsoft\Library\App\ApiApp;
use Madsoft\Library\Invoker;
use Madsoft\Library\Router;

require_once __DIR__ . '/../vendor/autoload.php';

$routesExt = [];
require 'routes.api.php';

$invoker = new Invoker();
$routes = $invoker->getInstance(Router::class)->loadRoutes(
    array_merge(
        [
            __DIR__ . '/Library/routes/api.accounts.routes.php',
            __DIR__ . '/Library/routes/api.crud.routes.php',
            __DIR__ . '/Library/routes/api.my-contents.routes.php',
        ],
        $routesExt
    ),
    'api'
);
$api = new ApiApp($invoker);
$api->setRoutes($routes);
$api->run();
