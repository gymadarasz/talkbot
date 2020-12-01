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

use Madsoft\Library\Account\AccountValidator;
use Madsoft\Library\Crud;
use Madsoft\Library\Logger;
use Madsoft\Library\Messages;
use Madsoft\Library\Params;
use Madsoft\Library\Responder\ArrayResponder;
use Madsoft\Library\User;

/**
 * Login
 *
 * @category  PHP
 * @package   Madsoft\Library\Account
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */
class Login extends ArrayResponder
{
    protected Crud $crud;
    protected Logger $logger;
    protected User $user;
    protected AccountValidator $validator;
    
    /**
     * Method __construct
     *
     * @param Messages         $messages  messages
     * @param Crud             $crud      crud
     * @param Logger           $logger    logger
     * @param User             $user      user
     * @param AccountValidator $validator validator
     */
    public function __construct(
        Messages $messages,
        Crud $crud,
        Logger $logger,
        User $user,
        AccountValidator $validator
    ) {
        parent::__construct($messages);
        $this->crud = $crud;
        $this->logger = $logger;
        $this->user = $user;
        $this->validator = $validator;
    }

    /**
     * Method getLoginResponse
     *
     * @param Params $params params
     *
     * @return mixed[]
     *
     * @suppress PhanUnreferencedPublicMethod
     */
    public function getLoginResponse(Params $params): array
    {
        $email = $params->get('email', '');
        
        $errors = $this->validator->validateLogin($params);
        if ($errors) {
            return $this->loginError($errors, $email);
        }
        
        $user = $this->crud->getRow(
            'user',
            ['id', 'email', 'group', 'hash'],
            ['email' => $email]
        );
        
        
        $errors = $this->validator->validateUser(
            $user,
            $params->get('password', '')
        );
        if ($errors) {
            return $this->loginError($errors, $email);
        }
        
        $this->user->login((int)($user['id'] ?? ''), $user['group'] ?? '');
        
        return $this->getSuccessResponse(
            'Login success'
        );
    }

    /**
     * Method loginError
     *
     * @param string[][]  $reasons reasons
     * @param string|null $email   email
     *
     * @return mixed[]
     */
    protected function loginError(array $reasons, ?string $email = null): array
    {
        $reasonstr = '';
        foreach ($reasons as $field => $errors) {
            $reasonstr .= " field '$field', error(s): '"
                    . implode("', '", $errors) . "'";
        }
        $this->logger->error(
            "Login error, reason:$reasonstr" . ($email ? ", email: '$email'" : '')
        );
        return $this->getErrorResponse(
            'Login failed'
        );
    }
}
