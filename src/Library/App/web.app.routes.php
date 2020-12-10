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
use Madsoft\Library\Account\View\PasswordChangeForm;
use Madsoft\Library\Account\View\PasswordResetForm;
use Madsoft\Library\Account\View\RegistryForm;
use Madsoft\Library\Layout\Layout;
use Madsoft\Library\Layout\View\Header;
use Madsoft\Library\Layout\View\Meta;
use Madsoft\Library\Layout\View\Navbar;
use Madsoft\Library\Layout\View\WelcomePage;

return $routes = [
    'public' => [
        'GET' => [
            '' => [
                'class' => Layout::class,
                'method' => 'getOutput',
                'overrides' => [
                    'tplfile' => 'index.phtml',
                    'title' => 'Welcome',
                    'header' => 'Welcome',
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
                    'title' => 'Login',
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
                    'title' => 'Registration',
                    'views' => [
                        'meta' => [Meta::class, 'getMeta'],
                        'navbar' => [Navbar::class, 'getNavbar'],
                        'header' => [Header::class, 'getHeader'],
                        'body' => [RegistryForm::class, 'getRegistryForm'],
                    ],
                ],
            ],
            'activate' => [
                'class' => ActivatePage::class,
                'method' => 'getOutput',
                'overrides' => [
                    'tplfile' => 'index.phtml',
                    'title' => 'Account activation',
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
                    'title' => 'Password reset',
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
                    'title' => 'Change password',
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
        ],
    ],
    'protected' => [
        
    ],
];
