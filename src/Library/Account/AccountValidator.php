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

use Madsoft\Library\Params;
use Madsoft\Library\Validator\Rule\Email;
use Madsoft\Library\Validator\Rule\Mandatory;
use Madsoft\Library\Validator\Rule\Match;
use Madsoft\Library\Validator\Rule\Number;
use Madsoft\Library\Validator\Rule\Password;
use Madsoft\Library\Validator\Rule\PasswordVerify;
use Madsoft\Library\Validator\Rule\Sleep;
use Madsoft\Library\Validator\Validator;

/**
 * AccountValidator
 *
 * @category  PHP
 * @package   Madsoft\Library\Account
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */
class AccountValidator extends Validator
{
    const LOGIN_DELAY = 0; // TODO: set to 3;
    
    const PASSWORD_VALIDATION = [
        'minLength' => ['min' => 8,],
        'checkMinLength' => true,
        'checkHasLower' => true,
        'checkHasUpper' => true,
        'checkHasNumber' => true,
        'checkHasSpecChar' => true,
    ];

    /**
     * Method validateLogin
     *
     * @param Params $params params
     *
     * @return string[][]
     */
    public function validateLogin(Params $params): array
    {
        $errors = $this->getErrors(
            [
                'delay' =>
                [
                    'value' => $this::LOGIN_DELAY,
                    'rules' =>
                    [
                        //Mandatory::class => null, // TODO add it on live
                        Sleep::class => null,
                    ],
                ],
            ]
        );
        if ($errors) {
            return $errors;
        }
        
        $errors = $this->getFirstError(
            [
                'email' => [
                    'value' => $params->get('email', ''),
                    'rules' => [
                        Mandatory::class => null,
                        Email::class => null,
                    ],
                ],
                'password' => [
                    'value' => $params->get('password', ''),
                    'rules' => [
                        Mandatory::class => null,
                        Password::class => self::PASSWORD_VALIDATION
                    ],
                ],
            ]
        );
        
        return $errors;
    }
    
    /**
     * Method validateUser
     *
     * @param string[] $user     user
     * @param string   $password password
     *
     * @return string[][]
     */
    public function validateUser(array $user, string $password): array
    {
        $errors = $this->getErrors(
            [
                'id' =>
                [
                    'value' => $user['id'],
                    'rules' =>
                    [
                        Mandatory::class => null,
                        Number::class => null,
                    ],
                ],
                'email' =>
                [
                    'value' => $user['email'],
                    'rules' =>
                    [
                        Mandatory::class => null,
                        Email::class => null,
                    ],
                ],
                'hash' =>
                [
                    'value' => $user['hash'],
                    'rules' =>
                    [
                        Mandatory::class => null,
                        PasswordVerify::class => ['password' => $password],
                    ],
                ],
            ]
        );
        return $errors;
    }
    
    /**
     * Method validateActivate
     *
     * @param Params $params params
     *
     * @return string[][]
     */
    public function validateActivate(Params $params): array
    {
        $errors = $this->getErrors(
            [
            'token' => [
                'value' => $params->get('token', ''),
                'rules' => [
                    Mandatory::class => null
                ],
            ],
            ]
        );
        return $errors;
    }
    
    /**
     * Method validatePasswordReset
     *
     * @param Params $params params
     *
     * @return string[][]
     */
    public function validatePasswordReset(Params $params): array
    {
        $errors = $this->getErrors(
            [
                'email' => [
                    'value' => $params->get('email', ''),
                    'rules' => [
                        Mandatory::class => null,
                        Email::class => null
                    ]
                ],
            ]
        );
        return $errors;
    }
    
    /**
     * Method validatePasswordChange
     *
     * @param Params $params params
     *
     * @return string[][]
     */
    public function validatePasswordChange(Params $params): array
    {
        $password = $params->get('password', '');
        $errors = $this->getErrors(
            [
                'token' => [
                    'value' => $params->get('token', ''),
                    'rules' => [
                        Mandatory::class => null,
                    ]
                ],
                'password' => [
                    'value' => $password,
                    'rules' => [
                        Mandatory::class => null,
                        Password::class => self::PASSWORD_VALIDATION,
                    ],
                ],
                'password_retype' => [
                    'value' => $params->get('password_retype', ''),
                    'rules' => [
                        Match::class => ['equalTo' => $password]
                    ]
                ],
            ]
        );
        return $errors;
    }
}
