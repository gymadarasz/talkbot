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
use Madsoft\Library\Token;

/**
 * PasswordReset
 *
 * @category  PHP
 * @package   Madsoft\Library\Account
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */
class PasswordReset extends ArrayResponder
{
    // TODO email should not contains api links
    // TODO add index.php instead (see in configs) - dependency (needs front-end)
    
    protected Token $token;
    protected Database $database;
    protected AccountValidator $validator;
    protected AccountMailer $mailer;

    /**
     * Method __construct
     *
     * @param Messages         $messages  messages
     * @param Csrf             $csrf      csrf
     * @param Token            $token     token
     * @param Database         $database  database
     * @param AccountValidator $validator validator
     * @param AccountMailer    $mailer    mailer
     */
    public function __construct(
        Messages $messages,
        Csrf $csrf,
        Token $token,
        Database $database,
        AccountValidator $validator,
        AccountMailer $mailer
    ) {
        parent::__construct($messages, $csrf);
        $this->token = $token;
        $this->database = $database;
        $this->validator = $validator;
        $this->mailer = $mailer;
    }

    /**
     * Method getPasswordResetRequestResponse
     *
     * @param Params $params params
     *
     * @return mixed[]
     *
     * @suppress PhanUnreferencedPublicMethod
     */
    public function getPasswordResetRequestResponse(Params $params): array
    {
        $errors = $this->validator->validatePasswordReset($params);
        // TODO exception handling for validations
        if ($errors) {
            return $this->getErrorResponse(
                'Reset password request failed',
                $errors
            );
        }
        
        $email = $params->get('email');
        
        if (!$this->database->getRow(
            'user',
            ['email'],
            '',
            '',
            ['email' => $email, 'active' => 1]
        )
        ) {
            return $this->getErrorResponse('Email address not found');
        }
        
        $token = $this->token->generate();
        if (!$this->database->setRow(
            'user',
            ['token' => $token],
            '',
            ['email' => $email, 'active' => 1]
        )
        ) {
            return $this->getErrorResponse('Token is not updated');
        }
        
        if (!$this->mailer->sendResetEmail($email, $token)) {
            // TODO exception handling for emails
            return $this->getErrorResponse(
                'Email sending failed'
            );
        }
        
        return $this->getSuccessResponse(
            'Password reset request email sent'
        );
    }
}
