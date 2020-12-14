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
use Madsoft\Library\Validator\Rule\Enum;
use Madsoft\Library\Validator\Rule\Mandatory;
use Madsoft\Library\Validator\Rule\MinLength;
use Madsoft\Library\Validator\Rule\Number;

$merger = new Merger();

$validations = [
    'filter.id' => [
        'value' => '{{ params: filter.id }}',
        'rules' => [Mandatory::class => null, Number::class => null],
    ],
];

$createValidation = [
    'talks' => [
        'value' => '{{ params: values.talks }}',
        'rules' => [
            Mandatory::class => null,
            Enum::class => ['values' => ['robot', 'human']],
        ],
    ],
    'text' => [
        'value' => '{{ params: values.text }}',
        'rules' => [Mandatory::class => null, MinLength::class => ['min' => 1]],
    ],
];

$editValidations = $merger->merge($createValidation, $validations);

$overrides = [
    'table' => 'script',
    'join' => 'JOIN content ON content.id = script.content_id',
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

$editOverrides = $overrides;

$editOverrides['values'] = [
    'content_id' => <<<SQL
        sql:
            (SELECT id FROM content
                WHERE id = '{{ params: values.content_id }}'
                AND owner_user_id = '{{ session: user.id }}')
    SQL,
];


return $routes = [
    'public' => [
        'GET' => [
            'script/list' => [
                'class' => Crud::class,
                'method' => 'getListResponse',
                'overrides' => $publicOverrides,
            ],
            'script/view' => [
                'class' => Crud::class,
                'method' => 'getViewResponse',
                'validations' => $validations,
                'overrides' => $publicOverrides,
            ],
        ],
    ],
    'protected' => [
        'GET' => [
            'my-script/list' => [
                'class' => Crud::class,
                'method' => 'getListResponse',
                'overrides' => $overrides,
            ],
            'my-script/view' => [
                'class' => Crud::class,
                'method' => 'getViewResponse',
                'validations' => $validations,
                'overrides' => $overrides,
            ],
            'my-script/delete' => [
                'class' => Crud::class,
                'method' => 'getDeleteResponse',
                'validations' => $validations,
                'overrides' => $overrides,
            ],
        ],
        'POST' => [
            'my-script/edit' => [
                'class' => Crud::class,
                'method' => 'getEditResponse',
                'validations' => $editValidations,
                'overrides' => $editOverrides,
            ],
            'my-script/create' => [
                'class' => Crud::class,
                'method' => 'getCreateResponse',
                'validations' => [
                    'talks' => [
                        'value' => '{{ params: values.talks }}',
                        'rules' => [
                            Mandatory::class => null,
                            Enum::class => ['values' => ['robot', 'human']],
                        ],
                    ],
                    'text' => [
                        'value' => '{{ params: values.text }}',
                        'rules' => [
                            Mandatory::class => null,
                            MinLength::class => ['min' => 1]],
                    ],
                ],
                'overrides' => [
                    'table' => 'script',
                    'values' => [
                        'owner_user_id' => '{{ session: user.id }}'
                    ],
                ],
            ],
        ],
    ],
];
