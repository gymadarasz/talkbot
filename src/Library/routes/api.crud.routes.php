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

use Madsoft\Library\Responder\ArrayResponder;
use Madsoft\Library\Validator\Rule\Mandatory;

return $routes = [
    'private' => [ // for admins
        'GET' => [
            'list' => [
                'class' => Crud::class,
                'method' => 'getListResponse',
                'validations' => [
                    'table' => [
                        'value' => '{{ params:table }}',
                        'rules' => [
                            Mandatory::class => null,
                        ],
                    ],
                ],
                'defaults' => [
                    'table' => '',
                ],
            ],
            'view' => [
                'class' => Crud::class,
                'method' => 'getViewResponse',
                'validations' => [
                    'table' => [
                        'value' => '{{ params:table }}',
                        'rules' => [
                            Mandatory::class => null,
                        ],
                    ],
                ],
                'defaults' => [
                    'table' => '',
                ],
            ],
            'delete' => [
                'class' => Crud::class,
                'method' => 'getDeleteResponse',
                'validations' => [
                    'table' => [
                        'value' => '{{ params:table }}',
                        'rules' => [
                            Mandatory::class => null,
                        ],
                    ],
                ],
                'defaults' => [
                    'table' => '',
                ],
                'overrides' => [
                    'onSuccessRedirectTarget' => null,
                ],
            ],
        ],
        'POST' => [
            'edit' => [
                'class' => Crud::class,
                'method' => 'getEditResponse',
                'validations' => [
                    'table' => [
                        'value' => '{{ params:table }}',
                        'rules' => [
                            Mandatory::class => null,
                        ],
                    ],
                ],
                'defaults' => [
                    'table' => '',
                    'filterLogic' => 'AND',
                    'limit' => 1,
                ],
                'overrides' => [
                    'onSuccessRedirectTarget' => null,
                ],
            ],
            'create' => [
                'class' => Crud::class,
                'method' => 'getCreateResponse',
                'validations' => [
                    'table' => [
                        'value' => '{{ params:table }}',
                        'rules' => [
                            Mandatory::class => null,
                        ],
                    ],
                ],
                'defaults' => [
                    'table' => '',
                ],
                'overrides' => [
                    'onSuccessRedirectTarget' => null,
                    'successMessage' => ArrayResponder::LBL_SUCCESS
                ],
            ],
        ],
    ],
];
