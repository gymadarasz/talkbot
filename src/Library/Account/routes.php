<?php declare(strict_types = 1);

/**
 * PHP version 7.4
 *
 * @category  PHP
 * @package   Madsoft\Library\Account
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */

namespace Madsoft\Library\Account;

use Madsoft\Library\Account\Activate;
use Madsoft\Library\Account\Login;
use Madsoft\Library\Account\Logout;
use Madsoft\Library\Account\PasswordChange;
use Madsoft\Library\Account\PasswordReset;
use Madsoft\Library\Account\Registry;

return $routes = [
    'public' => [ // for unautorized visitors
        'GET' => [
            'resend' => [
                'class' => Registry::class,
                'method' => 'getResendResponse',
            ],
            'activate' => [
                'class' => Activate::class,
                'method' => 'getActivateResponse',
            ],
            'password-reset' => [
                'class' => PasswordReset::class,
                'method' => 'getPasswordResetResponse',
            ],
        ],
        'POST' => [
            'login' => [
                'class' => Login::class,
                'method' => 'getLoginResponse',
            ],
            'registry' => [
                'class' => Registry::class,
                'method' => 'getRegistryResponse',
            ],
            'password-reset-request' => [
                'class' => PasswordReset::class,
                'method' => 'getPasswordResetRequestResponse'
            ],
            'password-change' => [
                'class' => PasswordChange::class,
                'method' => 'getPasswordChangeResponse',
            ],
        ],
    ],
    'protected' => [ // for users
        'GET' => [
            'logout' => [
                'class' => Logout::class,
                'method' => 'getLogoutResponse',
            ],
        ],
    ],
    'private' => [ // for admins
        'GET' => [
            'logout' => [
                'class' => Logout::class,
                'method' => 'getLogoutResponse',
            ],
        ],
    ],
];
