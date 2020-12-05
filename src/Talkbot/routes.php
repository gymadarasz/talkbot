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

return $routes = [
    'protected' => [
        'GET' => [
            'script/list' => [
                'class' => Crud::class,
                'method' => 'getListOwnedResponse',
                'overrides' => ['table' => 'script'],
            ],
            'script/view' => [
                'class' => Crud::class,
                'method' => 'getViewOwnedResponse',
                'validations' =>
                [
                    'filter.id' => [
                        'value' => '{{ filter.id }}',
                        'rules' => [Number::class => null]
                    ],
                ],
                'overrides' => ['table' => 'script'],
            ],
            'script/delete' => [
                'class' => Crud::class,
                'method' => 'getDeleteOwnedResponse',
                'validations' =>
                [
                    'filter.id' => [
                        'value' => '{{ filter.id }}',
                        'rules' => [Number::class => null]
                    ],
                ],
                'overrides' => ['table' => 'script'],
            ],
        ],
        'POST' => [
            'script/edit' => [
                'class' => Crud::class,
                'method' => 'getEditOwnedResponse',
                'validations' =>
                [
                    'filter.id' => [
                        'value' => '{{ filter.id }}',
                        'rules' => [Number::class => null]
                    ],
                ],
                'overrides' => ['table' => 'script'],
            ],
            'script/create' => [
                'class' => Crud::class,
                'method' => 'getCreateOwnedResponse',
                'validations' =>
                [
                    'values.name' => [
                        'value' => '{{ values.name }}',
                        'rules' => [MinLength::class => ['min' => 5]]
                    ],
                ],
                'overrides' => ['table' => 'script'],
            ],
        ],
    ],
];
