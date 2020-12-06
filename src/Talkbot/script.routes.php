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

use Madsoft\Library\Crud\Crud;
use Madsoft\Library\Validator\Rule\MinLength;
use Madsoft\Library\Validator\Rule\Number;

$overrides = [
    'table' => 'script',
    'filter' => ['owner_user_id' => '{{ session:user.id }}'],
    'values' => ['owner_user_id' => '{{ session:user.id }}'],
];

$validations = [
    'filter.id' => [
        'value' => '{{ params:filter.id }}',
        'rules' => [Number::class => null]
    ],
];

return $routes = [
    'protected' => [
        'GET' => [
            'script/list' => [
                'class' => Crud::class,
                'method' => 'getListResponse',
                'overrides' => $overrides,
            ],
            'script/view' => [
                'class' => Crud::class,
                'method' => 'getViewResponse',
                'validations' => $validations,
                'overrides' => $overrides,
            ],
            'script/delete' => [
                'class' => Crud::class,
                'method' => 'getDeleteResponse',
                'validations' => $validations,
                'overrides' => $overrides,
            ],
        ],
        'POST' => [
            'script/edit' => [
                'class' => Crud::class,
                'method' => 'getEditResponse',
                'validations' => $validations,
                'overrides' => $overrides,
            ],
            'script/create' => [
                'class' => Crud::class,
                'method' => 'getCreateResponse',
                'validations' =>
                [
                    'values.name' => [
                        'value' => '{{ params:values.name }}',
                        'rules' => [MinLength::class => ['min' => 5]]
                    ],
                ],
                'overrides' => $overrides,
            ],
        ],
    ],
];
