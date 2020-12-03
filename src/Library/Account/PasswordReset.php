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
use Madsoft\Library\Mailer;
use Madsoft\Library\Messages;
use Madsoft\Library\MysqlNotFoundException;
use Madsoft\Library\Params;
use Madsoft\Library\Responder\ArrayResponder;
use Madsoft\Library\Template;
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
    const EMAIL_TPL_PATH = __DIR__ . '/';
    
    // TODO email should not contains api links
    // TODO add index.php instead (see in configs)
    
    protected Template $template;
    protected Token $token;
    protected Database $database;
    protected AccountValidator $validator;
    protected Mailer $mailer;
    protected Logger $logger;

    /**
     * Method __construct
     *
     * @param Messages         $messages  messages
     * @param Template         $template  template
     * @param Token            $token     token
     * @param Database         $database  database
     * @param AccountValidator $validator validator
     * @param Mailer           $mailer    mailer
     * @param Logger           $logger    logger
     */
    public function __construct(
        Messages $messages,
        Template $template,
        Token $token,
        Database $database,
        AccountValidator $validator,
        Mailer $mailer,
        Logger $logger
    ) {
        parent::__construct($messages);
        $this->template = $template;
        $this->token = $token;
        $this->database = $database;
        $this->validator = $validator;
        $this->mailer = $mailer;
        $this->logger = $logger;
    }
    
    /**
     * Method getPasswordResetResponse
     *
     * @param Params $params params
     *
     * @return mixed[]
     *
     * @suppress PhanUnreferencedPublicMethod
     */
    public function getPasswordResetResponse(Params $params): array
    {
        $token = $params->get('token', '');
        if (!$token) {
            $this->getErrorResponse('Missing token');
        }
        try {
            $this->database->getRow(
                'user',
                ['id'],
                ['token' => $token, 'active' => '1']
            );
        } catch (MysqlNotFoundException $exception) {
            $this->logger->exception($exception);
            return $this->getErrorResponse(
                'Invalid token'
            );
        }
        
        // TODO recreate token before sanding back for usage
        return $this->getSuccessResponse(
            'Token matches',
            ['token' => $token]
        );
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
        if ($errors) {
            return $this->getErrorResponse(
                'Reset password request failed',
                $errors
            );
        }
        
        $email = (string)$params->get('email');
        
        try {
            $this->database->getRow(
                'user',
                ['email'],
                ['email' => $email, 'active' => '1']
            );
        } catch (MysqlNotFoundException $exception) {
            $this->logger->exception($exception);
            return $this->getErrorResponse(
                'Email address not found'
            );
        }
        
        $token = $this->token->generate();
        if (!$this->database->setRow(
            'user',
            ['token' => $token],
            ['email' => $email, 'active' => '1']
        )
        ) {
            return $this->getErrorResponse(
                'Token is not updated'
            );
        }
        
        if (!$this->sendResetEmail($email, $token)) {
            return $this->getErrorResponse(
                'Email sending failed'
            );
        }
        
        return $this->getSuccessResponse(
            'Password reset request email sent'
        );
    }
    
    /**
     * Method sendResetEmail
     *
     * @param string $email email
     * @param string $token token
     *
     * @return bool
     */
    protected function sendResetEmail(string $email, string $token): bool
    {
        $message = $this->template->process(
            'emails/reset.phtml',
            ['token' => $token],
            $this::EMAIL_TPL_PATH
        );
        return $this->mailer->send(
            $email,
            'Pasword reset requested',
            $message
        );
    }
}
