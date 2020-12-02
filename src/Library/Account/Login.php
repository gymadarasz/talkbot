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
use Madsoft\Library\Database;
use Madsoft\Library\Logger;
use Madsoft\Library\Messages;
use Madsoft\Library\Mysql;
use Madsoft\Library\Params;
use Madsoft\Library\Responder\ArrayResponder;
use Madsoft\Library\Throwier;
use Madsoft\Library\User;
use RuntimeException;

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
    protected Throwier $throwier;
    
    /**
     * Method __construct
     *
     * @param Messages         $messages  messages
     * @param Database         $database  database
     * @param Logger           $logger    logger
     * @param User             $user      user
     * @param AccountValidator $validator validator
     * @param Throwier         $throwier  throwier
     */
    public function __construct(
        Messages $messages,
        Database $database,
        Logger $logger,
        User $user,
        AccountValidator $validator,
        Throwier $throwier
    ) {
        parent::__construct($messages);
        $this->database = $database;
        $this->logger = $logger;
        $this->user = $user;
        $this->validator = $validator;
        $this->throwier = $throwier;
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
        
        try {
            $user = $this->database->getRow(
                'user',
                ['id', 'email', 'group', 'hash'],
                ['email' => $email, 'active' => '1']
            );
        } catch (RuntimeException $exception) {
            if ($exception->getcode() !== Mysql::MYSQL_ERROR) {
                $this->throwier->throwPrevious($exception);
            }
            $user = [];
        }
        
        
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
