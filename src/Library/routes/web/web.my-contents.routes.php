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
use Madsoft\Library\Layout\View\EditForm;
use Madsoft\Library\Layout\View\Header;
use Madsoft\Library\Layout\View\Meta;
use Madsoft\Library\Layout\View\Navbar;
use Madsoft\Library\Layout\View\TableList;
use Madsoft\Library\Router;

return $routes = [
    'protected' => [
        'GET' => [
            'my-contents' => [
                'class' => Layout::class,
                'method' => 'getOutput',
                'overrides' => [
                    'tplfile' => 'index.phtml',
                    'favicon' => 'favicons/favicon.ico',
                    'title' => 'My Contents',
                    'header' => 'My Contents',
                    'description' => 'My Contents',
                    'views' => [
                        'meta' => [Meta::class, 'getMeta'],
                        'navbar' => [Navbar::class, 'getNavbar'],
                        'header' => [Header::class, 'getHeader'],
                        'body' => [TableList::class, 'getList'],
                    ],
                    'table-list' => [
                        'title' => 'Content list',
                        'listId' => 'myContentList',
                        'apiEndPoint' => 'my-contents/list',
                        'columns' => [
                            [
                                'text' => 'Selection',
                                'field' => null,
                                'actions' => null,
                            ],
                            [
                                'text' => 'Head',
                                'field' => null,
                                'actions' => [
                                    [
                                        'type' => 'link',
                                        'title' => 'View {{ title }}',
                                        'text' => '{{ header }}',
                                        'href' => '?' . Router::ROUTE_QUERY_KEY .
                                            '=content&content[id]={{ id }}',
                                        'fields' => [
                                            'id',
                                            'title',
                                            'header'
                                        ],
                                    ],
                                ],
                            ],
                            [
                                'text' => 'Description',
                                'field' => 'description',
                                'actions' => null,
                            ],
                            [
                                'text' => 'Published',
                                'field' => null,
                                'actions' => [
                                    [
                                        'type' => 'tick',
                                        'value' => '{{ published }}',
                                        'text' => [
                                            '-', 'Published'
                                        ],
                                        'fields' => [
                                            'published'
                                        ],
                                    ]
                                ],
                            ],
                            [
                                'text' => 'Actions',
                                'field' => null,
                                'actions' => [
                                    [
                                        'type' => 'link',
                                        'title' => 'Edit {{ title }}',
                                        'text' => 'Edit',
                                        'href' => '?' . Router::ROUTE_QUERY_KEY .
                                            '=my-contents/edit&id={{ id }}',
                                        'fields' => ['id', 'title'],
                                    ],
                                    [
                                        'type' => 'link',
                                        'title' => 'Delete {{ title }}',
                                        'text' => 'Delete',
                                        'href' => '?' . Router::ROUTE_QUERY_KEY .
                                            '=my-contents/delete&id={{ id }}',
                                        'fields' => ['id', 'title'],
                                    ],
                                ],
                            ],
                        ],
                        'tools' => [
                            [
                                'text' => 'Refresh list',
                                'link' => 'my-contents'
                            ],
                            [
                                'text' => 'New..',
                                'link' => 'my-contents/create'
                            ],
                        ],
                    ],
                ],
            ],
            'my-contents/create' => [
                'class' => Layout::class,
                'method' => 'getOutput',
                'overrides' => [
                    'tplfile' => 'index.phtml',
                    'favicon' => 'favicons/favicon.ico',
                    'title' => 'My Contents',
                    'header' => 'My Contents',
                    'description' => 'My Contents / create',
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
                                'id' => 'title',
                                'label' => 'Title',
                                'name' => 'values[title]',
                                'placeholder' => 'Type content title here..',
                            ],
                            [
                                'type' => 'input',
                                'id' => 'description',
                                'label' => 'Description',
                                'name' => 'values[description]',
                                'placeholder' => 'Type content description here..',
                            ],
                            [
                                'type' => 'input',
                                'id' => 'header',
                                'label' => 'Header',
                                'name' => 'values[header]',
                                'placeholder' => 'Type content header here..',
                            ],
                            [
                                'type' => 'input',
                                'id' => 'body',
                                'label' => 'Body',
                                'name' => 'values[body]',
                                'placeholder' => 'Type content body here..',
                            ],
                        ],
                        'formId' => 'myContentCreateForm',
                        'title' => 'My Contents / Create',
                        'apiEndPoint' => 'my-contents/create',
                        'submitButtonLabel' => 'Create'
                    ],
                ],
            ],
            'my-contents/edit' => [
                'class' => Layout::class,
                'method' => 'getOutput',
                'overrides' => [
                    'tplfile' => 'index.phtml',
                    'favicon' => 'favicons/favicon.ico',
                    'title' => 'My Contents',
                    'header' => 'My Contents',
                    'description' => 'My Contents / Edit',
                    'views' => [
                        'meta' => [Meta::class, 'getMeta'],
                        'navbar' => [Navbar::class, 'getNavbar'],
                        'header' => [Header::class, 'getHeader'],
                        'body' => [EditForm::class, 'getEditForm'],
                    ],
                    'edit-form' => [
                        'fields' => [
                            [
                                'type' => 'hidden',
                                'id' => 'id',
                                'name' => 'values[id]',
                                'value' => '{{ params: id }}'
                            ],
                            [
                                'type' => 'input',
                                'id' => 'title',
                                'label' => 'Title',
                                'name' => 'values[title]',
                                'placeholder' => 'Type content title here..',
                            ],
                            [
                                'type' => 'input',
                                'id' => 'description',
                                'label' => 'Description',
                                'name' => 'values[description]',
                                'placeholder' => 'Type content description here..',
                            ],
                            [
                                'type' => 'input',
                                'id' => 'header',
                                'label' => 'Header',
                                'name' => 'values[header]',
                                'placeholder' => 'Type content header here..',
                            ],
                            [
                                'type' => 'input',
                                'id' => 'body',
                                'label' => 'Body',
                                'name' => 'values[body]',
                                'placeholder' => 'Type content body here..',
                            ],
                            [
                                'type' => 'checkbox',
                                'id' => 'published',
                                'label' => 'Published',
                                'name' => 'values[published]',
                            ],
                        ],
                        'formId' => 'myContentEditForm',
                        'title' => 'My Contents / Edit',
                        'apiEndPoint' => 'my-contents/edit',
                        'submitButtonLabel' => 'Save',
                        'formBindKey' => 'values',
                        'dataset' => [
                            'table' => 'content',
                            'fields' => [
                                'id',
                                'title',
                                'description',
                                'header',
                                'body',
                                'published'
                            ],
                            'join' => 'JOIN user ON user.id = content.owner_user_id',
                            'where' => 'AND content.id = {{ params: id }}',
                            'filter' => [
                                'content.id' => '{{ params: id }}',
                                'owner_user_id' => '{{ session: user.id }}',
                            ],
                            'filterLogic' => 'AND',
                            'limit' => 1,
                            'offset' => 0,
                        ],
                    ],
                ],
            ]
        ],
    ],
];
