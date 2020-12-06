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
use Madsoft\Library\Merger;
use Madsoft\Library\Validator\Rule\Mandatory;
use Madsoft\Library\Validator\Rule\Number;

$merger = new Merger();

$validations = [
    'filter.id' => [
        'value' => '{{ params: filter.id }}',
        'rules' => [Mandatory::class => null, Number::class => null]
    ],
];

$createValidation = [
    'values.parent_script_id' => [
        'value' => '{{ params: values.text }}',
        'rules' => [Mandatory::class => null, Number::class => null]
    ],
    'values.child_script_id' => [
        'value' => '{{ params: values.text }}',
        'rules' => [Mandatory::class => null, Number::class => null]
    ],
];

$editValidations = $merger->merge($createValidation, $validations);

$overrides = [
    'table' => 'script_to_script',
    'join' => 'JOIN script child_script '
            . 'ON child_script.id = script_to_script.child_script_id '
            . 'JOIN script parent_script '
            . 'ON parent_script.id = script_to_script.parent_script_id '
            . 'JOIN content '
            . 'ON content.id = child_script.content_id '
            . 'AND content.id = parent_script.content_id',
    
    'filter' => ['content.owner_user_id' => '{{ session: user.id }}'],
    'values' => ['content.owner_user_id' => '{{ session: user.id }}'],
];

$publicOverrides = $merger->merge(
    $overrides,
    [
        'where' => 'OR content.published = 1',
    ]
);

$createOverrides = $overrides;
$createOverrides['values'] = [
    'parent_script_id' => '('
            . 'SELECT id FROM script '
            . 'JOIN content ON script.content_id = content.id '
            . 'AND content.owner_user_id = {{ session: user.id }}'
            . 'WHERE script.id = {{ params: values.parent_script_id }} '
        . ')',
    'child_script_id' => '('
            . 'SELECT id FROM script '
            . 'JOIN content ON script.content_id = content.id '
            . 'AND content.owner_user_id = {{ session: user.id }}'
            . 'WHERE script.id = {{ params: values.child_script_id }} '
        . ')',
];
$createOverrides['noQuotes'] = ['parent_script_id', 'child_script_id'];

return $routes = [
    'public' => [
        'GET' => [
            'script_to_script/list' => [
                'class' => Crud::class,
                'method' => 'getListResponse',
                'overrides' => $publicOverrides,
            ],
            'script_to_script/view' => [
                'class' => Crud::class,
                'method' => 'getViewResponse',
                'validations' => $validations,
                'overrides' => $publicOverrides,
            ],
        ],
    ],
    'protected' => [
        'GET' => [
            'script_to_script/delete' => [
                'class' => Crud::class,
                'method' => 'getDeleteResponse',
                'validations' => $validations,
                'overrides' => $overrides,
            ],
        ],
        'POST' => [
            'script_to_script/edit' => [
                'class' => Crud::class,
                'method' => 'getEditResponse',
                'validations' => $editValidations,
                'overrides' => $overrides,
            ],
            'script_to_script/create' => [
                'class' => Crud::class,
                'method' => 'getCreateResponse',
                'validations' => $createValidation,
                'overrides' => $overrides,
            ],
        ],
    ],
];
