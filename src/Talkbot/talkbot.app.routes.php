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

use Madsoft\Library\Layout\Layout;
use Madsoft\Library\Layout\View\CreateForm;
use Madsoft\Library\Layout\View\Header;
use Madsoft\Library\Layout\View\Meta;
use Madsoft\Library\Layout\View\Navbar;
use Madsoft\Library\Layout\View\TableList;

return $routes = [
    'protected' => [
        'GET' => [
            'my-scripts' => [
                'class' => Layout::class,
                'method' => 'getOutput',
                'overrides' => [
                    'tplfile' => 'index.phtml',
                    'favicon' => 'favicons/favicon.ico',
                    'title' => 'My Scripts',
                    'views' => [
                        'meta' => [Meta::class, 'getMeta'],
                        'navbar' => [Navbar::class, 'getNavbar'],
                        'header' => [Header::class, 'getHeader'],
                        'body' => [TableList::class, 'getList'],
                    ],
                    'table-list' => [
                        'title' => 'My Scripts',
                        'listId' => 'myScriptList',
                        'apiEndPoint' => 'my-script/list',
                        'columns' => [
                            ['text' => 'Selection'],
                            ['text' => 'Script'],
                            ['text' => 'Actions'],
                        ],
                        'actions' => [
                            ['text' => 'Refresh list', 'link' => 'my-scripts'],
                            ['text' => 'New..', 'link' => 'my-script/create'],
                        ],
                    ],
                ],
            ],
            'my-script/create' => [
                'class' => Layout::class,
                'method' => 'getOutput',
                'defaults' => [
                    'values' => [
                        'talks' => '',
                    ],
                ],
                'overrides' => [
                    'tplfile' => 'index.phtml',
                    'favicon' => 'favicons/favicon.ico',
                    'title' => 'My Scripts / Create',
                    'views' => [
                        'meta' => [Meta::class, 'getMeta'],
                        'navbar' => [Navbar::class, 'getNavbar'],
                        'header' => [Header::class, 'getHeader'],
                        'body' => [CreateForm::class, 'getCreateForm'],
                    ],
                    'create-form' => [
                        'formId' => 'myScriptCreate',
                        'title' => 'Create script',
                        'fields' => [
                            [
                                'type' => 'select',
                                'id' => 'talks',
                                'name' => 'values[talks]',
                                'label' => 'Talks',
                                'placeholder' => '-- Please select... --',
                                'selected' => '{{ params: values.talks }}',
                                'options' => [
                                    ['value' => 'robot', 'text' => 'Robot'],
                                    ['value' => 'human', 'text' => 'Human'],
                                ],
                            ],
                            [
                                'type' => 'input',
                                'id' => 'text',
                                'name' => 'values[text]',
                                'label' => 'Text',
                                'placeholder' => 'Text',
                            ],
                        ],
                        'apiEndPoint' => 'my-script/create',
                        'submitButtonLabel' => 'Create',
                    ]
                ],
            ],
        ],
    ],
];
