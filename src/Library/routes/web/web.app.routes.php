<?php declare(strict_types = 1);

/**
 * PHP version 7.4
 *
 * @category  PHP
 * @package   Madsoft\Library\App
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */

namespace Madsoft\Library\App;

use Madsoft\Library\Account\View\ActivatePage;
use Madsoft\Library\Account\View\LoginForm;
use Madsoft\Library\Account\View\LogoutPage;
use Madsoft\Library\Account\View\PasswordChangeForm;
use Madsoft\Library\Account\View\PasswordResetForm;
use Madsoft\Library\Account\View\RegistryForm;
use Madsoft\Library\Layout\Layout;
use Madsoft\Library\Layout\View\ContentPage;
use Madsoft\Library\Layout\View\Header;
use Madsoft\Library\Layout\View\Meta;
use Madsoft\Library\Layout\View\Navbar;
use Madsoft\Library\Layout\View\TableList;
use Madsoft\Library\Layout\View\WelcomePage;
use Madsoft\Library\Router;

return $routes = [
    'public' => [
        'GET' => [
            '' => [
                'class' => Layout::class,
                'method' => 'getOutput',
                'overrides' => [
                    'tplfile' => 'index.phtml',
                    'favicon' => 'favicons/favicon.ico',
                    'title' => 'Content list',
                    'header' => 'Content list',
                    'description' => 'Content list of {{ config.site: brand }} page',
                    'views' => [
                        'meta' => [Meta::class, 'getMeta'],
                        'navbar' => [Navbar::class, 'getNavbar'],
                        'header' => [Header::class, 'getHeader'],
                        'body' => [TableList::class, 'getList'],
                    ],
                    'table-list' => [
                        'title' => 'Content list',
                        'listId' => 'contentList',
                        'apiEndPoint' => 'content/list',
                        'columns' => [
                            [
                                'text' => 'Header',
                                'field' => null,
                                'actions' => [
                                    [
                                        'type' => 'link',
                                        'title' => 'View {{ title }}',
                                        'text' => '{{ header }}',
                                        'href' => '?' . Router::ROUTE_QUERY_KEY .
                                            '=content&content[id]={{ id }}',
                                        'fields' => ['id', 'title', 'header'],
                                    ],
                                ],
                            ],
                            [
                                'text' => 'Description',
                                'field' => 'description',
                                'actions' => null,
                            ],
                        ],
                        'tools' => [
                            // TODO share like rate claps.. etc
                        ]
                    ],
                ],
            ],
            'welcome' => [
                'class' => Layout::class,
                'method' => 'getOutput',
                'overrides' => [
                    'tplfile' => 'index.phtml',
                    'favicon' => 'favicons/favicon.ico',
                    'title' => 'Welcome',
                    'header' => 'Welcome',
                    'description' => 'Welcome on {{ config.site: brand }} page',
                    'views' => [
                        'meta' => [Meta::class, 'getMeta'],
                        'navbar' => [Navbar::class, 'getNavbar'],
                        'header' => [Header::class, 'getHeader'],
                        'body' => [WelcomePage::class, 'getPublicArea'],
                    ],
                ],
            ],
            'login' => [
                'class' => Layout::class,
                'method' => 'getOutput',
                'overrides' => [
                    'tplfile' => 'index.phtml',
                    'favicon' => 'favicons/favicon.ico',
                    'title' => 'Login',
                    'header' => 'Login',
                    'description' => 'Login to {{ config.site: brand }} page',
                    'views' => [
                        'meta' => [Meta::class, 'getMeta'],
                        'navbar' => [Navbar::class, 'getNavbar'],
                        'header' => [Header::class, 'getHeader'],
                        'body' => [LoginForm::class, 'getLoginForm'],
                    ],
                ],
            ],
            'registry' => [
                'class' => Layout::class,
                'method' => 'getOutput',
                'overrides' => [
                    'tplfile' => 'index.phtml',
                    'favicon' => 'favicons/favicon.ico',
                    'title' => 'Registration',
                    'header' => 'Registration',
                    'description' => 'Registration to {{ config.site: brand }} page',
                    'views' => [
                        'meta' => [Meta::class, 'getMeta'],
                        'navbar' => [Navbar::class, 'getNavbar'],
                        'header' => [Header::class, 'getHeader'],
                        'body' => [RegistryForm::class, 'getRegistryForm'],
                    ],
                ],
            ],
            'activate' => [
                'class' => Layout::class,
                'method' => 'getOutput',
                'overrides' => [
                    'tplfile' => 'index.phtml',
                    'favicon' => 'favicons/favicon.ico',
                    'title' => 'Account activation',
                    'header' => 'Account activation',
                    'description' => 'Account activation'
                    . ' to {{ config.site: brand }} page',
                    'views' => [
                        'meta' => [Meta::class, 'getMeta'],
                        'navbar' => [Navbar::class, 'getNavbar'],
                        'header' => [Header::class, 'getHeader'],
                        'body' => [ActivatePage::class, 'getActivatePage'],
                    ],
                ],
            ],
            'password-reset' => [
                'class' => Layout::class,
                'method' => 'getOutput',
                'overrides' => [
                    'tplfile' => 'index.phtml',
                    'favicon' => 'favicons/favicon.ico',
                    'title' => 'Password reset',
                    'header' => 'Password reset',
                    'description' => 'Password reset'
                    . ' for {{ config.site: brand }} page',
                    'views' => [
                        'meta' => [Meta::class, 'getMeta'],
                        'navbar' => [Navbar::class, 'getNavbar'],
                        'header' => [Header::class, 'getHeader'],
                        'body' => [PasswordResetForm::class, 'getPasswordResetForm'],
                    ],
                ],
            ],
            'password-change' => [
                'class' => Layout::class,
                'method' => 'getOutput',
                'overrides' => [
                    'tplfile' => 'index.phtml',
                    'favicon' => 'favicons/favicon.ico',
                    'title' => 'Change password',
                    'header' => 'Change password',
                    'description' => 'Change password'
                    . ' for {{ config.site: brand }} page',
                    'views' => [
                        'meta' => [Meta::class, 'getMeta'],
                        'navbar' => [Navbar::class, 'getNavbar'],
                        'header' => [Header::class, 'getHeader'],
                        'body' => [
                            PasswordChangeForm::class,
                            'getPasswordChangeForm'
                        ],
                    ],
                ],
            ],
            'content' => [
                'class' => Layout::class,
                'method' => 'getOutput',
                'overrides' => [
                    'tplfile' => 'index.phtml',
                    'favicon' => 'favicons/favicon.ico',
                    'views' => [
                        'meta' => [Meta::class, 'getMeta'],
                        'navbar' => [Navbar::class, 'getNavbar'],
                        'header' => [Header::class, 'getHeader'],
                        'body' => [
                            ContentPage::class,
                            'getContent'
                        ],
                    ],
                    'content-dataset' => [
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
                        'where' => ''
                        . 'content.id = {{ params: content.id }} AND '
                        . 'content.published = 1',
                        'filter' => [],
                        'filterLogic' => '',
                        'limit' => 1,
                        'offset' => 0,
                    ],
                ],
            ],
        ],
    ],
    'protected' => [
        'GET' => [
            'logout' => [
                'class' => Layout::class,
                'method' => 'getOutput',
                'overrides' => [
                    'tplfile' => 'index.phtml',
                    'favicon' => 'favicons/favicon.ico',
                    'title' => 'Logging out...',
                    'header' => 'Logging out...',
                    'description' => 'Logging out'
                    . ' from {{ config.site: brand }} page',
                    'views' => [
                        'meta' => [Meta::class, 'getMeta'],
                        'navbar' => [Navbar::class, 'getNavbar'],
                        'header' => [Header::class, 'getHeader'],
                        'body' => [LogoutPage::class, 'getLogout'],
                    ],
                    'redirectTarget' => 'login',
                ],
            ],
            'content' => [
                'overrides' => [
                    'content-dataset' => [
                        'where' => '('
                        . 'content.id = {{ params: content.id }} AND '
                        . 'content.published = 1'
                        . ') OR content.owner_user_id = {{ session: user.id }}',
                    ],
                ],
            ],
        ],
    ],
];
