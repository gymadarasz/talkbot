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
use Madsoft\Library\Logger;
use Madsoft\Library\Messages;
use Madsoft\Library\MysqlNoAffectException;
use Madsoft\Library\MysqlNotFoundException;
use Madsoft\Library\Params;
use Madsoft\Library\Responder\ArrayResponder;
use Madsoft\Library\Session;

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
    protected Logger $logger;


    /**
     * Method __construct
     *
     * @param Messages         $messages  messages
     * @param Database         $database  database
     * @param AccountValidator $validator validator
     * @param Logger           $logger    logger
     */
    public function __construct(
        Messages $messages,
        Database $database,
        AccountValidator $validator,
        Logger $logger
    ) {
        parent::__construct($messages);
        $this->database = $database;
        $this->validator = $validator;
        $this->logger = $logger;
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
        } catch (MysqlNotFoundException $exception) {
            $this->logger->exception($exception);
            return $this->getErrorResponse(
                'Invalid token'
            );
        }
        
        try {
            $this->database->setRow(
                'user',
                ['active' => '1'],
                ['token' => $token]
            );
        } catch (MysqlNoAffectException $exception) {
            $this->logger->exception($exception);
            return $this->getErrorResponse(
                'User activation failed'
            );
        }
        
        $session->unset('resend');
        
        return $this->getSuccessResponse(
            'Account is now activated'
        );
    }
}
