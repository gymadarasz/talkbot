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
use Madsoft\Library\Validator\Rule\Email;
use Madsoft\Library\Validator\Rule\Mandatory;
use Madsoft\Library\Validator\Rule\Match;
use Madsoft\Library\Validator\Rule\Password;

return $routes = [
    'public' => [ // for unautorized visitors
        'GET' => [
            'resend' => [
                'class' => Resend::class,
                'method' => 'getResendResponse',
            ],
            'activate' => [
                'class' => Activate::class,
                'method' => 'getActivateResponse',
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
                'defaults' =>
                [
                    'email' => '',
                    'email_retype' => '',
                    'password' => ''
                ],
                'validations' => [
                    'email' =>
                    [
                        'value' => '{{ params: email }}',
                        'rules' =>
                        [
                            Mandatory::class => null,
                            Email::class => null,
                        ],
                    ],
                    'password' =>
                    [
                        'value' => '{{ params: password }}',
                        'rules' =>
                        [
                            Mandatory::class => null,
                            Password::class => [
                                'minLength' => ['min' => 8,],
                                'checkMinLength' => true,
                                'checkHasLower' => true,
                                'checkHasUpper' => true,
                                'checkHasNumber' => true,
                                'checkHasSpecChar' => true,
                            ],
                        ],
                    ],
                    'email_retype' => [
                        'value' => '{{ params: email_retype }}',
                        'rules' => [
                            Mandatory::class => null,
                            Match::class => ['equalTo' => '{{ params: email }}'],
                        ],
                    ],
                ]
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
