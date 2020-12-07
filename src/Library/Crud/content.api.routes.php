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
use Madsoft\Library\Merger;
use Madsoft\Library\Validator\Rule\Mandatory;
use Madsoft\Library\Validator\Rule\MinLength;
use Madsoft\Library\Validator\Rule\Number;

$merger = new Merger();

$defaults = [
    'filter' => ['id' => ''],
];

$validations = [
    'filter.id' => [
        'value' => '{{ params: filter.id }}',
        'rules' => [Mandatory::class => null, Number::class => null]
    ],
];

$createValidations = [
    'values.name' => [
        'value' => '{{ params: values.name }}',
        'rules' => [Mandatory::class => null, MinLength::class => ['min' => 1]]
    ],
];

$editValidations = $merger->merge($createValidations, $validations);

$overrides = [
    'table' => 'content',
    'filter' => ['owner_user_id' => '{{ session: user.id }}'],
    'values' => ['owner_user_id' => '{{ session: user.id }}'],
];

$publicOverrides = $merger->merge(
    $overrides,
    [
        'where' => 'OR content.published = 1',
    ]
);

return $routes = [
    'public' => [
        'GET' => [
            'content/list' => [
                'class' => Crud::class,
                'method' => 'getListResponse',
                'overrides' => $publicOverrides,
            ],
            'content/view' => [
                'class' => Crud::class,
                'method' => 'getViewResponse',
                'defaults' => $defaults,
                'validations' => $validations,
                'overrides' => $publicOverrides,
            ],
        ],
    ],
    'protected' => [
        'GET' => [
            'content/delete' => [
                'class' => Crud::class,
                'method' => 'getDeleteResponse',
                'defaults' => $defaults,
                'validations' => $validations,
                'overrides' => $overrides,
            ],
        ],
        'POST' => [
            'content/edit' => [
                'class' => Crud::class,
                'method' => 'getEditResponse',
                'defaults' => $defaults,
                'validations' => $editValidations,
                'overrides' => $overrides,
            ],
            'content/create' => [
                'class' => Crud::class,
                'method' => 'getCreateResponse',
                'validations' => $createValidations,
                'overrides' => $overrides,
            ],
        ],
    ],
];
