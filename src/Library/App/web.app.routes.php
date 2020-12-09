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
                    'views' => [
                        'header' => [Header::class, 'getHeader'],
                        'navbar' => [Navbar::class, 'getPublicNavbar'],
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
                        'header' => [Header::class, 'getHeader'],
                        'navbar' => [Navbar::class, 'getPublicNavbar'],
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
                        'header' => [Header::class, 'getHeader'],
                        'navbar' => [Navbar::class, 'getPublicNavbar'],
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
                        'header' => [Header::class, 'getHeader'],
                        'navbar' => [Navbar::class, 'getPublicNavbar'],
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
                        'header' => [Header::class, 'getHeader'],
                        'navbar' => [Navbar::class, 'getPublicNavbar'],
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
                        'header' => [Header::class, 'getHeader'],
                        'navbar' => [Navbar::class, 'getPublicNavbar'],
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
