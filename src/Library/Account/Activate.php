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
use Madsoft\Library\Messages;
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

    /**
     * Method __construct
     *
     * @param Messages                                  $messages  messages
     * @param Csrf                                      $csrf      csrf
     * @param Session                                   $session   session
     * @param Database                                  $database  database
     * @param \Madsoft\Library\Account\AccountValidator $validator validator
     */
    public function __construct(
        Messages $messages,
        Csrf $csrf,
        Session $session,
        Database $database,
        AccountValidator $validator
    ) {
        parent::__construct($messages, $csrf, $session);
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
        
        if (!$this->database->getRow(
            'user',
            ['id'],
            '',
            '',
            ['token' => $token, 'active' => 0]
        )
        ) {
            return $this->getErrorResponse('Invalid token');
        }
                
        if ($this->database->setRow(
            'user',
            ['active' => 1],
            '',
            ['token' => $token]
        ) <= 0
        ) {
            return $this->getErrorResponse('User activation failed');
        }
        
        $session->unset('resend');
        
        return $this->getSuccessResponse(
            'Account is now activated'
        );
    }
}
