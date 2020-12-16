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
use Madsoft\Library\Csrf;
use Madsoft\Library\Database;
use Madsoft\Library\Logger;
use Madsoft\Library\Messages;
use Madsoft\Library\Params;
use Madsoft\Library\Responder\ArrayResponder;
use Madsoft\Library\Session;
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
    protected Database $database;
    protected Logger $logger;
    protected User $user;
    protected AccountValidator $validator;
    
    /**
     * Method __construct
     *
     * @param Messages         $messages  messages
     * @param Csrf             $csrf      csrf
     * @param Session          $session   session
     * @param Database         $database  database
     * @param Logger           $logger    logger
     * @param User             $user      user
     * @param AccountValidator $validator validator
     */
    public function __construct(
        Messages $messages,
        Csrf $csrf,
        Session $session,
        Database $database,
        Logger $logger,
        User $user,
        AccountValidator $validator
    ) {
        parent::__construct($messages, $csrf, $session);
        $this->database = $database;
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
        $email = $params->get('email');
        
        $errors = $this->validator->validateLogin($params);
        if ($errors) {
            return $this->loginError($errors, $email, 'Invalid params');
        }
        
        $user = $this->database->getRow(
            'user',
            ['id', 'email', 'group', 'hash'],
            '',
            '',
            ['email' => $email, 'active' => 1]
        );
        if (!$user) {
            return $this->loginError([], $email, 'Not found');
        }
        
        $errors = $this->validator->validateUser(
            $user,
            $params->get('password')
        );
        if ($errors) {
            return $this->loginError($errors, $email, 'Invalid data');
        }
        
        $this->user->login($user['id'], $user['group']);
        
        return $this->getSuccessRedirectResponse(
            '',
            'Login success'
        );
    }

    /**
     * Method loginError
     *
     * @param string[][]  $reasons    reasons
     * @param string|null $email      email
     * @param string      $mainreason mainreason
     *
     * @return mixed[]
     */
    protected function loginError(
        array $reasons,
        ?string $email = null,
        string $mainreason = 'Unknown error'
    ): array {
        $reasonstr = '';
        foreach ($reasons as $field => $errors) {
            $reasonstr .= " field '$field', error(s): '"
                    . implode("', '", $errors) . "'";
        }
        $this->logger->fail(
            "Login failed, reason: $mainreason -$reasonstr"
                . ($email ? " (email: '$email')" : '')
        );
        return $this->getErrorResponse('Login failed');
    }
}
