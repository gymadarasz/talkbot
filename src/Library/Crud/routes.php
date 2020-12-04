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

return $routes = [
    'private' => [ // for admins
        'GET' => [
            'list' => [
                'class' => Crud::class,
                'method' => 'getListResponse',
            ],
            'view' => [
                'class' => Crud::class,
                'method' => 'getViewResponse',
            ],
            'delete' => [
                'class' => Crud::class,
                'method' => 'getDeleteResponse',
            ],
        ],
        'POST' => [
            'edit' => [
                'class' => Crud::class,
                'method' => 'getEditResponse',
            ],
            'create' => [
                'class' => Crud::class,
                'method' => 'getCreateResponse',
            ],
        ],
    ],
];
