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

use Madsoft\Library\Layout\Layout;
use Madsoft\Library\Layout\View\Header;
use Madsoft\Library\Layout\View\ListForm;
use Madsoft\Library\Layout\View\Meta;
use Madsoft\Library\Layout\View\Navbar;

return $routes = [
    'protected' => [
        'GET' => [
            'my-scripts' => [
                'class' => Layout::class,
                'method' => 'getOutput',
                'overrides' => [
                    'tplfile' => 'index.phtml',
                    'title' => 'My Scripts',
                    'views' => [
                        'meta' => [Meta::class, 'getMeta'],
                        'navbar' => [Navbar::class, 'getNavbar'],
                        'header' => [Header::class, 'getHeader'],
                        'body' => [ListForm::class, 'getList'],
                    ],
                    'list-form' => [
                        'api' => 'script/list',
                    ],
                ],
            ],
        ],
    ],
];
