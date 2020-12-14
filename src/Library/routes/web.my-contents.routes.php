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

use Madsoft\Library\Layout\Layout;
use Madsoft\Library\Layout\View\CreateForm;
use Madsoft\Library\Layout\View\Header;
use Madsoft\Library\Layout\View\Meta;
use Madsoft\Library\Layout\View\Navbar;
use Madsoft\Library\Layout\View\TableList;

return $routes = [
    'protected' => [
        'GET' => [
            'my-contents' => [
                'class' => Layout::class,
                'method' => 'getOutput',
                'overrides' => [
                    'tplfile' => 'index.phtml',
                    'favicon' => 'favicon.ico',
                    'title' => 'My Contents',
                    'views' => [
                        'meta' => [Meta::class, 'getMeta'],
                        'navbar' => [Navbar::class, 'getNavbar'],
                        'header' => [Header::class, 'getHeader'],
                        'body' => [TableList::class, 'getList'],
                    ],
                    'table-list' => [
                        'title' => 'My Contents',
                        'listId' => 'myContentList',
                        'apiEndPoint' => 'my-contents/list',
                        'columns' => [
                            ['text' => 'Selection', 'field' => null],
                            ['text' => 'Content', 'field' => 'name'],
                            ['text' => 'Actions', 'field' => null],
                        ],
                        'actions' => [
                            ['text' => 'Refresh list', 'link' => 'my-contents'],
                            ['text' => 'New..', 'link' => 'my-contents/create'],
                        ],
                    ],
                ],
            ],
            'my-contents/create' => [
                'class' => Layout::class,
                'method' => 'getOutput',
                'overrides' => [
                    'tplfile' => 'index.phtml',
                    'favicon' => 'favicon.ico',
                    'views' => [
                        'meta' => [Meta::class, 'getMeta'],
                        'navbar' => [Navbar::class, 'getNavbar'],
                        'header' => [Header::class, 'getHeader'],
                        'body' => [CreateForm::class, 'getCreateForm'],
                    ],
                    'create-form' => [
                        'fields' => [
                            [
                                'type' => 'input',
                                'id' => 'name',
                                'label' => 'Name',
                                'name' => 'values[name]',
                                'placeholder' => 'Type content name here..',
                            ],
                        ],
                        'formId' => 'myContentsCreateForm',
                        'title' => 'My Contents / Create',
                        'apiEndPoint' => 'my-contents/create',
                        'submitButtonLabel' => 'Create'
                    ],
                ],
            ]
        ],
        '*' => [
            '*' => [
                'overrides' => [
                    'navbar' => [
                        'extra' => [
                            'links' => [
                                'left' => [
                                    [
                                        'dropdown' => [],
                                        'disabled' => false,
                                        'href' => 'q=my-contents',
                                        'text' => 'My Contents',
                                    ]
                                ],
                                'right' => []
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
];
