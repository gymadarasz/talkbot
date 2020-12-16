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
use Madsoft\Library\Messages;
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
    protected AccountMailer $mailer;

    /**
     * Method __construct
     *
     * @param Messages      $messages  messages
     * @param Csrf          $csrf      csrf
     * @param Session       $session   session
     * @param Token         $token     token
     * @param Encrypter     $encrypter encrypter
     * @param Database      $database  database
     * @param AccountMailer $mailer    mailer
     */
    public function __construct(
        Messages $messages,
        Csrf $csrf,
        Session $session,
        Token $token,
        Encrypter $encrypter,
        Database $database,
        AccountMailer $mailer
    ) {
        parent::__construct($messages, $csrf, $session);
        $this->token = $token;
        $this->encrypter = $encrypter;
        $this->database = $database;
        $this->mailer = $mailer;
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
        $email = $params->get('email');
        $token = $this->token->generate();
        
        $user = $this->database->getRow(
            'user',
            ['email', 'active', 'token'],
            '',
            '',
            ['email' => $email]
        );
        
        if (!empty($user)) {
            if (!$user['active']) {
                return $this->getResendResponse(
                    $session,
                    $user,
                    $email,
                    $user['token']
                );
            }
            return $this->getErrorResponse('Email address already registered');
        }
        
        if (!$this->database->addRow(
            'user',
            [
                    'email' => $email,
                    'hash' => $this->encrypter->encrypt($params->get('password')),
                    'token' => $token,
                    'active' => 0,
                ]
        )
        ) {
            return $this->getErrorResponse('User is not saved');
        }
        
        return $this->getResendResponse(
            $session,
            $user,
            $email,
            $token
        );
    }
    
    /**
     * Method getResendResponse
     *
     * @param Session  $session session
     * @param string[] $user    user
     * @param string   $email   email
     * @param string   $token   token
     *
     * @return mixed[]
     */
    protected function getResendResponse(
        Session $session,
        array $user,
        string $email,
        string $token
    ): array {
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
