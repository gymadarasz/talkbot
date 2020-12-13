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
use Madsoft\Library\Messages;
use Madsoft\Library\Responder\ArrayResponder;
use Madsoft\Library\Session;

/**
 * Resend
 *
 * @category  PHP
 * @package   Madsoft\Library\Account
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */
class Resend extends ArrayResponder
{
    protected AccountMailer $mailer;
    
    /**
     * Method __construct
     *
     * @param Messages      $messages messages
     * @param Csrf          $csrf     csrf
     * @param Session       $session  session
     * @param AccountMailer $mailer   mailer
     */
    public function __construct(
        Messages $messages,
        Csrf $csrf,
        Session $session,
        AccountMailer $mailer
    ) {
        parent::__construct($messages, $csrf, $session);
        $this->mailer = $mailer;
    }
    /**
     * Method getResendResponse
     *
     * @param Session $session session
     *
     * @return mixed[]
     *
     * @suppress PhanUnreferencedPublicMethod
     */
    public function getResendResponse(Session $session): array
    {
        $resend = $session->get('resend', ['email' => '', 'token' => null]);
        $email = $resend['email'];
        $token = $resend['token'];
        if (!$this->mailer->sendActivationEmail($email, $token)) {
            return $this->getErrorResponse(
                'Activation email is not sent'
            );
        }
        
        return $this->getSuccessResponse(
            'We re-sent an activation email to your email account, '
                . 'please follow the instructions.'
        );
    }
}
