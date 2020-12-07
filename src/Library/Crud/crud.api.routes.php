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

use Madsoft\Library\Validator\Rule\Mandatory;

$defaults = [
    'table' => '',
];

$validations = [
    'table' => [
        'value' => '{{ params:table }}',
        'rules' => [
            Mandatory::class => null,
        ],
    ],
];

return $routes = [
    'private' => [ // for admins
        'GET' => [
            'list' => [
                'class' => Crud::class,
                'method' => 'getListResponse',
                'validations' => $validations,
                'defaults' => $defaults,
            ],
            'view' => [
                'class' => Crud::class,
                'method' => 'getViewResponse',
                'validations' => $validations,
                'defaults' => $defaults,
            ],
            'delete' => [
                'class' => Crud::class,
                'method' => 'getDeleteResponse',
                'validations' => $validations,
                'defaults' => $defaults,
            ],
        ],
        'POST' => [
            'edit' => [
                'class' => Crud::class,
                'method' => 'getEditResponse',
                'validations' => $validations,
                'defaults' => $defaults,
            ],
            'create' => [
                'class' => Crud::class,
                'method' => 'getCreateResponse',
                'validations' => $validations,
                'defaults' => $defaults,
            ],
        ],
    ],
];
