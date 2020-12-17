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

use Madsoft\Library\Csrf;
use Madsoft\Library\Database;
use Madsoft\Library\Encrypter;
use Madsoft\Library\Messages;
use Madsoft\Library\Params;
use Madsoft\Library\Responder\ArrayResponder;
use Madsoft\Library\Session;

/**
 * PasswordChange
 *
 * @category  PHP
 * @package   Madsoft\Library\Account
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */
class PasswordChange extends ArrayResponder
{
    protected Database $database;
    protected AccountValidator $validator;
    protected Encrypter $encrypter;

    /**
     * Method __construct
     *
     * @param Messages         $messages  messages
     * @param Csrf             $csrf      csrf
     * @param Session          $session   session
     * @param Database         $database  database
     * @param AccountValidator $validator validator
     * @param Encrypter        $encrypter encrypter
     */
    public function __construct(
        Messages $messages,
        Csrf $csrf,
        Session $session,
        Database $database,
        AccountValidator $validator,
        Encrypter $encrypter
    ) {
        parent::__construct($messages, $csrf, $session);
        $this->database = $database;
        $this->validator = $validator;
        $this->encrypter = $encrypter;
    }
    
    /**
     * Method getPasswordChangeResponse
     *
     * @param Params $params params
     *
     * @return mixed[]
     *
     * @suppress PhanUnreferencedPublicMethod
     */
    public function getPasswordChangeResponse(Params $params): array
    {
        $token = $params->get('token');
        
        $errors = $this->validator->validatePasswordChange($params);
        if ($errors) {
            return $this->getErrorResponse(
                'Password change failed',
                $errors,
                [
                    'token' => $token
                ]
            );
        }
        // TODO add test for sql injection escaping
        
        // TODO add test when token matches but user is inactive
        $user = $this->database->getRow(
            'user',
            ['hash'],
            '',
            '',
            ['token' => $token, 'active' => 1]
        );
        if (!$user) {
            return $this->getErrorResponse('User not found at the given token');
        }
        $hash = $this->encrypter->encrypt($params->get('password'));
        if ($user['hash'] === $hash) {
            // TODO add test when password is not changed
            return $this->getErrorResponse(
                'Same password already taken and not changed.'
            );
        }
        if ($this->database->setRow(
            'user',
            [
                    'hash' => $hash,
                    'token' => null,
                ],
            '',
            [
                    'token' => $params->get('token'),
                    'active' => 1,
                ]
        ) <= 0
        ) {
            return $this->getErrorResponse('Password is not saved');
        }
        
        return $this->getSuccessRedirectResponse(
            'login',
            'Password is changed'
        );
    }
}
