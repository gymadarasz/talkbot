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

use Madsoft\Library\Crud;
use Madsoft\Library\Mailer;
use Madsoft\Library\Messages;
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
    protected Crud $crud;
    protected AccountValidator $validator;
    protected Mailer $mailer;

    /**
     * Method __construct
     *
     * @param Messages         $messages  messages
     * @param Template         $template  template
     * @param Token            $token     token
     * @param Crud             $crud      crud
     * @param AccountValidator $validator validator
     * @param Mailer           $mailer    mailer
     */
    public function __construct(
        Messages $messages,
        Template $template,
        Token $token,
        Crud $crud,
        AccountValidator $validator,
        Mailer $mailer
    ) {
        parent::__construct($messages);
        $this->template = $template;
        $this->token = $token;
        $this->crud = $crud;
        $this->validator = $validator;
        $this->mailer = $mailer;
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
        $user = $this->crud->getRow('user', ['id'], ['token' => $token]);
        if (!($user['id'] ?? '')) {
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
        $user = $this->crud->getRow('user', ['email'], ['email' => $email]);
        if (($user['email'] ?? '') !== $email) {
            return $this->getErrorResponse(
                'Email address not found'
            );
        }
        
        $token = $this->token->generate();
        if (!$this->crud->setRow('user', ['token' => $token], ['email' => $email])) {
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
            [
            //                'base' => $this->config->get('Site')->get('base'),
                'token' => $token,
            ],
            $this::EMAIL_TPL_PATH
        );
        return $this->mailer->send(
            $email,
            'Pasword reset requested',
            $message
        );
    }
}
