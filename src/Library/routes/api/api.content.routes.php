<?php declare(strict_types = 1);

/**
 * PHP version 7.4
 *
 * @category  PHP
 * @package   Madsoft\Library\Crud
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */

namespace Madsoft\Library\Crud;

use Madsoft\Library\Crud\Crud;
use Madsoft\Library\Validator\Rule\Mandatory;
use Madsoft\Library\Validator\Rule\Number;

return $routes = [
    'public' => [
        'GET' => [
            'content/list' => [
                'class' => Crud::class,
                'method' => 'getListResponse',
                'defaults' => ['offset' => 0],
                'overrides' => [
                    'table' => 'content',
                    'filter' => ['published' => 1],
                    'filterLogic' => 'AND',
                    'limit' => 25,
                ],
            ],
            'content/view' => [
                'class' => Crud::class,
                'method' => 'getViewResponse',
            //                'defaults' => [
            //                    'filter' => ['id' => ''],
            //                ],
                'validations' => [
                    'filter.id' => [
                        'value' => '{{ params: filter.id }}',
                        'rules' => [
                            Mandatory::class => null,
                            Number::class => null
                        ]
                    ],
                ],
                'overrides' => [
                    'table' => 'content',
                    'filter' => [
                        'published' => 1,
                    ],
                    'filterLogic' => 'AND',
                    'limit' => 1,
                    'offset' => 0,
                ],
            ],
        ],
    ],
];
