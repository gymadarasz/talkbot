<?php declare(strict_types = 1);

/**
 * PHP version 7.4
 *
 * @category  PHP
 * @package   Madsoft\Library\Testing
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */

namespace Madsoft\Library\Testing;

use Madsoft\Library\Layout\Layout;
use Madsoft\Library\Layout\View\Header;
use Madsoft\Library\Layout\View\Meta;
use Madsoft\Library\Layout\View\Navbar;
use Madsoft\Library\Testing\Testing;

// TODO catch js errors
return $routes = [
    'public' => [
        'GET' => [
            'testing/mails/list' => [
                'class' => Layout::class,
                'method' => 'getOutput',
                'overrides' => [
                    'tplfile' => 'index.phtml',
                    'favicon' => 'favicons/favicon.ico',
                    'title' => 'Mails testing',
                    'header' => 'Mails testing',
                    'views' => [
                        'meta' => [Meta::class, 'getMeta'],
                        'navbar' => [Navbar::class, 'getNavbar'],
                        'header' => [Header::class, 'getHeader'],
                        'body' => [
                            Testing::class,
                            'getMailsStringResponse',
                        ],
                    ],
                ],
            ],
            'testing/mails/delete-all' => [
                'class' => Layout::class,
                'method' => 'getOutput',
                'overrides' => [
                    'tplfile' => 'index.phtml',
                    'favicon' => 'favicons/favicon.ico',
                    'title' => 'Mails testing',
                    'header' => 'Mails testing',
                    'views' => [
                        'meta' => [Meta::class, 'getMeta'],
                        'navbar' => [Navbar::class, 'getNavbar'],
                        'header' => [Header::class, 'getHeader'],
                        'body' => [
                            Testing::class,
                            'deleteMailsStringResponse',
                        ],
                    ],
                ],
            ],
            'testing/mails/view' => [
                'class' => Layout::class,
                'method' => 'getOutput',
                'overrides' => [
                    'tplfile' => 'index.phtml',
                    'favicon' => 'favicons/favicon.ico',
                    'title' => 'Mails testing',
                    'header' => 'Mails testing',
                    'views' => [
                        'meta' => [Meta::class, 'getMeta'],
                        'navbar' => [Navbar::class, 'getNavbar'],
                        'header' => [Header::class, 'getHeader'],
                        'body' => [
                            Testing::class,
                            'getMailStringResponse',
                        ],
                    ],
                ],
            ],
        ],
        'POST' => [
            'testing/mails/list' => [
                'class' => Layout::class,
                'method' => 'getOutput',
                'overrides' => [
                    'tplfile' => 'index.phtml',
                    'favicon' => 'favicons/favicon.ico',
                    'title' => 'Mails testing',
                    'header' => 'Mails testing',
                    'views' => [
                        'meta' => [Meta::class, 'getMeta'],
                        'navbar' => [Navbar::class, 'getNavbar'],
                        'header' => [Header::class, 'getHeader'],
                        'body' => [
                            Testing::class,
                            'deleteUser',
                        ],
                    ],
                ],
            ]
        ],
    ],
];
