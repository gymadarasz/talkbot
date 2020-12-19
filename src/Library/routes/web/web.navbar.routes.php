<?php declare(strict_types = 1);

/**
 * PHP version 7.4
 *
 * @category  PHP
 * @package   Madsoft\Library\routes
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */

namespace Madsoft\Library\routes;

return $routes = [
    'public' => [
        '*' => [
            '*' => [
                'overrides' => [
                    'navbar' => [
                        'extra' => [
                            'links' => [
                                'left' => [],
                                'right' => []
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'protected' => [
        '*' => [
            '*' => [
                'overrides' => [
                    'navbar' => [
                        'extra' => [
                            'links' => [
                                'left' => [
                                    [
                                        'dropdown' => [],
                                        'disabled' => false,
                                        'href' => 'q=my-contents',
                                        'text' => 'My Contents',
                                    ]
                                ],
                                'right' => []
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
];
