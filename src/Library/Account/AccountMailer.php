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

use Madsoft\Library\Config;
use Madsoft\Library\Logger;
use Madsoft\Library\Mailer;
use Madsoft\Library\Template;

/**
 * AccountMailer
 *
 * @category  PHP
 * @package   Madsoft\Library\Account
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */
class AccountMailer extends Mailer
{
    const EMAIL_TPL_PATH = __DIR__ . '/../phtml/account/';
    
    protected Template $template;
    
    /**
     * Method __construct
     *
     * @param Config   $config   config
     * @param Logger   $logger   logger
     * @param Template $template template
     */
    public function __construct(
        Config $config,
        Logger $logger,
        Template $template
    ) {
        parent::__construct($config, $logger);
        $this->template = $template;
    }
    /**
     * Method sendActivationEmail
     *
     * @param string $email email
     * @param string $token token
     *
     * @return bool
     */
    public function sendActivationEmail(string $email, string $token): bool
    {
        $message = $this->template->process(
            'emails/activation.phtml',
            [
            //                'base' => $this->config->get('Site')->get('base'),
                'token' => $token,
            ],
            $this::EMAIL_TPL_PATH
        );
        return $this->send(
            $email,
            'Activate your account',
            $message
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
    public function sendResetEmail(string $email, string $token): bool
    {
        $message = $this->template->process(
            'emails/reset.phtml',
            ['token' => $token],
            $this::EMAIL_TPL_PATH
        );
        return $this->send(
            $email,
            'Pasword reset requested',
            $message
        );
    }
}
