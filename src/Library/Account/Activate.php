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
use Madsoft\Library\Messages;
use Madsoft\Library\Mysql;
use Madsoft\Library\Params;
use Madsoft\Library\Responder\ArrayResponder;
use Madsoft\Library\Session;
use RuntimeException;

/**
 * Activate
 *
 * @category  PHP
 * @package   Madsoft\Library\Account
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */
class Activate extends ArrayResponder
{
    protected Database $database;
    protected AccountValidator $validator;
    
    /**
     * Method __construct
     *
     * @param Messages         $messages  messages
     * @param Database         $database  database
     * @param AccountValidator $validator validator
     */
    public function __construct(
        Messages $messages,
        Database $database,
        AccountValidator $validator
    ) {
        parent::__construct($messages);
        $this->database = $database;
        $this->validator = $validator;
    }
    
    /**
     * Method getActivateResponse
     *
     * @param Params  $params  params
     * @param Session $session session
     *
     * @return mixed[]
     *
     * @suppress PhanUnreferencedPublicMethod
     */
    public function getActivateResponse(Params $params, Session $session): array
    {
        $errors = $this->validator->validateActivate($params);
        if ($errors) {
            return $this->getErrorResponse(
                'Account activation failed',
                $errors
            );
        }
        
        $token = $params->get('token');
        
        try {
            $this->database->getRow(
                'user',
                ['id'],
                ['token' => $token, 'active' => '0']
            );
        } catch (RuntimeException $exception) {
            if ($exception->getCode() === Mysql::MYSQL_ERROR) {
                return $this->getErrorResponse(
                    'Invalid token'
                );
            }
            throw new RuntimeException(
                'Database error: ' . $exception->getMessage()
                    . $exception->getMessage()
                    . ' (' . $exception->getCode() . ')',
                (int)$exception->getCode(),
                $exception
            );
        }
        
        try {
            $this->database->setRow(
                'user',
                ['active' => '1'],
                ['token' => $token]
            );
        } catch (RuntimeException $exception) {
            if ($exception->getCode() === Mysql::MYSQL_ERROR) {
                return $this->getErrorResponse(
                    'User activation failed'
                );
            }
            throw new RuntimeException(
                'Database error: ' . $exception->getMessage()
                    . $exception->getMessage()
                    . ' (' . $exception->getCode() . ')',
                (int)$exception->getCode(),
                $exception
            );
        }
        
        $session->unset('resend');
        
        return $this->getSuccessResponse(
            'Account is now activated'
        );
    }
}
