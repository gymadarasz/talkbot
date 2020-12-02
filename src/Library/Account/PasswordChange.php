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
use Madsoft\Library\Database;
use Madsoft\Library\Encrypter;
use Madsoft\Library\Messages;
use Madsoft\Library\Responder\ArrayResponder;

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
     * @param Database         $database  database
     * @param AccountValidator $validator validator
     * @param Encrypter        $encrypter encrypter
     */
    public function __construct(
        Messages $messages,
        Database $database,
        AccountValidator $validator,
        Encrypter $encrypter
    ) {
        parent::__construct($messages);
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
        // TODO do not let the user to use the same password again
        $errors = $this->validator->validatePasswordChange($params);
        if ($errors) {
            return $this->getErrorResponse(
                'Password change failed',
                $errors,
                [
                    'token' => $params->get('token')
                ]
            );
        }
        
        if (!$this->database->setRow(
            'user',
            [
                'hash' => $this->encrypter->encrypt($params->get('password')),
                'token' => '',
            ],
            [
                'token' => $params->get('token'),
                'active' => '1',
            ]
        )
        ) {
            return $this->getErrorResponse(
                'Password is not saved',
                [],
                [
                    'token' => $params->get('token')
                ]
            );
        }
        
        return $this->getSuccessResponse(
            'Password is changed'
        );
    }
}
