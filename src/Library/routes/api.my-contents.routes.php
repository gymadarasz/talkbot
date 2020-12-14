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

use Madsoft\Library\Crud\Crud;
use Madsoft\Library\Validator\Rule\Mandatory;

return $routes = [
    'protected' => [
        'GET' => [
            'my-contents/list' => [
                'class' => Crud::class,
                'method' => 'getListResponse',
                'overrides' => [
                    'table' => 'content',
                    'filter' => [
                        'owner_user_id' => '{{ session: user.id }}'
                    ],
                ]
            ]
        ],
        'POST' => [
            'my-contents/create' => [
                'class' => Crud::class,
                'method' => 'getCreateResponse',
                'overrides' => [
                    'table' => 'content',
                    'values' => [
                        'owner_user_id' => '{{ session: user.id }}',
                    ],
                    'successMessage' => 'Content saved',
                    'onSuccessRedirectTarget' => 'my-contents',
                ],
                'validations' => [
                    'name' => [
                        'value' => '{{ params: values.name }}',
                        'rules' => [Mandatory::class => null]
                    ]
                ]
            ]
        ]
    ]
];
