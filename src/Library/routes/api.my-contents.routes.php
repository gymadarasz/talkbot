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
use Madsoft\Library\Responder\ArrayResponder;
use Madsoft\Library\Validator\Rule\Mandatory;
use Madsoft\Library\Validator\Rule\Number;

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
            ],
            // My delete should be only an update on deleted column
            'my-contents/delete' => [
                'class' => Crud::class,
                'method' => 'getDeleteResponse',
                'defaults' => [
                    'filter' => ['id' => ''],
                ],
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
                    'filter' => ['owner_user_id' => '{{ session: user.id }}'],
                    'values' => ['owner_user_id' => '{{ session: user.id }}'],
                    'successMessage' => ArrayResponder::LBL_SUCCESS,
                    'onSuccessRedirectTarget' => null,
                ]
            ],
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
                        'rules' => [
                            Mandatory::class => null
                        ]
                    ]
                ]
            ],
            'my-contents/edit' => [
                'class' => Crud::class,
                'method' => 'getEditResponse',
                'overrides' => [
                    'table' => 'content',
                    'values' => [
                        'owner_user_id' => '{{ session: user.id }}',
                    ],
                    'filter' => [
                        'content.id' => '{{ params: values.id }}',
                        'owner_user_id' => '{{ session: user.id }}',
                    ],
                    'filterLogic' => 'AND',
                    'limit' => 1,
                    'successMessage' => 'Content saved',
                    'noAffectMessage' => 'Content is not changed',
                    'onSuccessRedirectTarget' => 'my-contents',
                ],
                'validations' => [
                    'id' => [
                        'value' => '{{ params: values.id }}',
                        'rules' => [
                            Mandatory::class => null,
                            Number::class => null
                        ]
                    ],
                    'name' => [
                        'value' => '{{ params: values.name }}',
                        'rules' => [
                            Mandatory::class => null
                        ]
                    ],
                ]
            ]
        ],
    ]
];
