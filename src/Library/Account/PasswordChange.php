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

use Madsoft\Library\Database;
use Madsoft\Library\Encrypter;
use Madsoft\Library\Logger;
use Madsoft\Library\Messages;
use Madsoft\Library\MysqlNoAffectException;
use Madsoft\Library\MysqlNotFoundException;
use Madsoft\Library\Params;
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
    protected Logger $logger;


    /**
     * Method __construct
     *
     * @param Messages         $messages  messages
     * @param Database         $database  database
     * @param AccountValidator $validator validator
     * @param Encrypter        $encrypter encrypter
     * @param Logger           $logger    logger
     */
    public function __construct(
        Messages $messages,
        Database $database,
        AccountValidator $validator,
        Encrypter $encrypter,
        Logger $logger
    ) {
        parent::__construct($messages);
        $this->database = $database;
        $this->validator = $validator;
        $this->encrypter = $encrypter;
        $this->logger = $logger;
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
        
        try {
            // TODO add test when token matches but user is inactive
            $user = $this->database->getRow(
                'user',
                ['hash'],
                ['token' => $token, 'active' => 1]
            );
            $hash = $this->encrypter->encrypt($params->get('password'));
            if ($user['hash'] === $hash) {
                // TODO add test when password is not changed
                return $this->getErrorResponse(
                    'Same password already taken and not changed.'
                );
            }
            $this->database->setRow(
                'user',
                [
                    'hash' => $hash,
                    'token' => null,
                ],
                [
                    'token' => $params->get('token'),
                    'active' => 1,
                ]
            );
        } catch (MysqlNotFoundException $exception) {
            $this->logger->exception($exception);
            return $this->getErrorResponse('User not found at the given token');
        } catch (MysqlNoAffectException $exception) {
            $this->logger->exception($exception);
            return $this->getErrorResponse('Password is not saved');
        }
        
        return $this->getSuccessResponse(
            'Password is changed'
        );
    }
}
