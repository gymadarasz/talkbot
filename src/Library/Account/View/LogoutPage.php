<?php declare(strict_types = 1);

/**
 * PHP version 7.4
 *
 * @category  PHP
 * @package   Madsoft\Library\Account\View
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */

namespace Madsoft\Library\Account\View;

use Madsoft\Library\Config;
use Madsoft\Library\Session;
use Madsoft\Library\User;

/**
 * LogoutPage
 *
 * @category  PHP
 * @package   Madsoft\Library\Account\View
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */
class LogoutPage
{
    protected Config $config;
    protected User $user;
    protected Session $session;
    
    /**
     * Method __construct
     *
     * @param Config  $config  config
     * @param User    $user    user
     * @param Session $session session
     */
    public function __construct(Config $config, User $user, Session $session)
    {
        $this->config = $config;
        $this->user = $user;
        $this->session = $session;
    }
    
    /**
     * Method getLogout
     *
     * @return string
     *
     * @suppress PhanUnreferencedPublicMethod
     */
    public function getLogout(): string
    {
        $this->user->logout();
        // todo: session mesage when redirects
        $redirect = $this->config->get('Site')->get('base') . '/?q=login';
        // TODO add redirector
        $this->session->set(
            'message',
            [
                'type' => 'success',
                'text' => 'Logout sucess',
            ]
        );
        header("Location: $redirect");
        return "<script>document.location.href = '$redirect';</script>";
    }
}
