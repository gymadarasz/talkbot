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
use Madsoft\Library\Crud;
use Madsoft\Library\Messages;
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
    protected Crud $crud;
    protected AccountValidator $validator;
    
    /**
     * Method __construct
     *
     * @param Messages         $messages  messages
     * @param Crud             $crud      crud
     * @param AccountValidator $validator validator
     */
    public function __construct(
        Messages $messages,
        Crud $crud,
        AccountValidator $validator
    ) {
        parent::__construct($messages);
        $this->crud = $crud;
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
        
        $user = $this->crud->getRow(
            'user',
            ['id', 'active'],
            ['token' => $token]
        );
        if (!($user['id'] ?? '')) {
            return $this->getErrorResponse(
                'Invalid token'
            );
        }
        
        if ($user['active'] ?? '') {
            return $this->getErrorResponse(
                'User is active already'
            );
        }
        
        if (!$this->crud->setRow('user', ['active' => '1'], ['token' => $token])) {
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
