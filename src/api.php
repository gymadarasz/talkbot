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

$invoker = new Invoker();
$routes = $invoker->getInstance(Router::class)->loadRoutes(
    [
        __DIR__ . '/Library/Account/routes.php',
        __DIR__ . 'routes.api.php',
    ]
);
$api = new ApiApp($invoker);
$api->setRoutes($routes);
$api->run();
