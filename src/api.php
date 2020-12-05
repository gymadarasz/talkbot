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

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL | E_STRICT);

$routeset = [];
require 'routeset.php';

$invoker = new Invoker();
$routes = $invoker->getInstance(Router::class)->loadRoutes(
    array_merge(
        [
            __DIR__ . '/Library/Account/routes.php',
            __DIR__ . '/Library/Crud/routes.php',
            __DIR__ . '/Library/routes.php',
        ],
        $routeset
    )
);
$api = new ApiApp($invoker);
$api->setRoutes($routes);
$api->run();
