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
use Madsoft\Library\Validator\Rule\MinLength;
use Madsoft\Library\Validator\Rule\Number;

$validations = [
    'filter.id' => [
        'value' => '{{ params:filter.id }}',
        'rules' => [Number::class => null]
    ],
];

$createValidation = [
    'values.name' => [
        'value' => '{{ params:values.name }}',
        'rules' => [MinLength::class => ['min' => 5]]
    ],
];

$overrides = [
    'table' => 'content',
    'filter' => ['owner_user_id' => '{{ session:user.id }}'],
    'values' => ['owner_user_id' => '{{ session:user.id }}'],
];

$publicListOverrides = [
    'table' => 'content',
    'where' => 'OR content.published = 1',
    'filter' => ['owner_user_id' => '{{ session:user.id }}'],
    'values' => ['owner_user_id' => '{{ session:user.id }}'],
];

$publicViewOverrides = [
    'table' => 'content',
    'where' => 'OR content.published = 1',
    'filter' => ['owner_user_id' => '{{ session:user.id }}'],
    'values' => ['owner_user_id' => '{{ session:user.id }}'],
];

return $routes = [
    'public' => [
        'GET' => [
            'content/list' => [
                'class' => Crud::class,
                'method' => 'getListResponse',
                'overrides' => $publicListOverrides,
            ],
            'content/view' => [
                'class' => Crud::class,
                'method' => 'getViewResponse',
                'validations' => $validations,
                'overrides' => $publicViewOverrides,
            ],
        ],
    ],
    'protected' => [
        'GET' => [
            'content/delete' => [
                'class' => Crud::class,
                'method' => 'getDeleteResponse',
                'validations' => $validations,
                'overrides' => $overrides,
            ],
        ],
        'POST' => [
            'content/edit' => [
                'class' => Crud::class,
                'method' => 'getEditResponse',
                'validations' => $validations,
                'overrides' => $overrides,
            ],
            'content/create' => [
                'class' => Crud::class,
                'method' => 'getCreateResponse',
                'validations' => $createValidation,
                'overrides' => $overrides,
            ],
        ],
    ],
];
