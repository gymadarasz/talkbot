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

return $routes = [
    'protected' => [
        '*' => [
            '*' => [
                'overrides' => [
                    'navbar' => [
                        'extra' => [
                            'links' => [
                                'left' => [
                                    [
//                                        'active' =>
//                                            '{{ params: q }}' === 'my-scripts',
                                        'dropdown' => [],
                                        'disabled' => false,
                                        'href' => 'q=my-scripts',
                                        'text' => 'My Scripts',
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
