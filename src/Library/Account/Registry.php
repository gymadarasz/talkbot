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
use Madsoft\Library\Logger;
use Madsoft\Library\Messages;
use Madsoft\Library\MysqlNoInsertException;
use Madsoft\Library\MysqlNotFoundException;
use Madsoft\Library\Params;
use Madsoft\Library\Responder\ArrayResponder;
use Madsoft\Library\Session;
use Madsoft\Library\Token;

/**
 * Registry
 *
 * @category  PHP
 * @package   Madsoft\Library\Account
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */
class Registry extends ArrayResponder
{
    protected Token $token;
    protected Encrypter $encrypter;
    protected Database $database;
    protected AccountValidator $validator;
    protected AccountMailer $mailer;
    protected Logger $logger;

    /**
     * Method __construct
     *
     * @param Messages         $messages  messages
     * @param Csrf             $csrf      csrf
     * @param Token            $token     token
     * @param Encrypter        $encrypter encrypter
     * @param Database         $database  database
     * @param AccountValidator $validator validator
     * @param AccountMailer    $mailer    mailer
     * @param Logger           $logger    logger
     */
    public function __construct(
        Messages $messages,
        Csrf $csrf,
        Token $token,
        Encrypter $encrypter,
        Database $database,
        AccountValidator $validator,
        AccountMailer $mailer,
        Logger $logger
    ) {
        parent::__construct($messages, $csrf);
        $this->token = $token;
        $this->encrypter = $encrypter;
        $this->database = $database;
        $this->validator = $validator;
        $this->mailer = $mailer;
        $this->logger = $logger;
    }

    /**
     * Method getRegistryResponse
     *
     * @param Params  $params  params
     * @param Session $session session
     *
     * @return mixed[]
     *
     * @suppress PhanUnreferencedPublicMethod
     */
    public function getRegistryResponse(Params $params, Session $session): array
    {
        $errors = $this->validator->validateRegistry($params);
        if ($errors) {
            return $this->getErrorResponse(
                'Invalid registration data',
                $errors
            );
        }
        
        $email = $params->get('email');
        $token = $this->token->generate();
        
        
        try {
            $user = $this->database->getRow('user', ['email'], ['email' => $email]);
        } catch (MysqlNotFoundException $exception) {
            $this->logger->exception($exception);
            $user = [];
        }
        
        if (!empty($user)) {
            return $this->getErrorResponse('Email address already registered');
        }
        
        try {
            $this->database->addRow(
                'user',
                [
                    'email' => $email,
                    'hash' => $this->encrypter->encrypt($params->get('password')),
                    'token' => $token,
                    'active' => 0,
                ]
            );
        } catch (MysqlNoInsertException $exception) {
            $this->logger->exception($exception);
            return $this->getErrorResponse(
                'User is not saved'
            );
        }
        
        $session->set('resend', ['email' => $email, 'token' => $token]);
        
        if (!$this->mailer->sendActivationEmail($email, $token)) {
            return $this->getWarningResponse(
                'Activation email is not sent',
                $user
            );
        }
        
        return $this->getSuccessResponse(
            'We sent an activation email to your email account, '
                . 'please follow the instructions.'
        );
    }
}
