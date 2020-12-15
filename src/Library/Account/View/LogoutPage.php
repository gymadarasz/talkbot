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
use Madsoft\Library\Params;
use Madsoft\Library\Redirector;
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
    protected Redirector $redirector;
    protected Params $params;

    /**
     * 
     * @param Config $config
     * @param User $user
     * @param Session $session
     * @param Redirector $redirector
     * @param Params $params
     */
    public function __construct(
            Config $config, User $user, Session $session, Redirector $redirector, Params $params)
    {
        $this->config = $config;
        $this->user = $user;
        $this->session = $session;
        $this->redirector = $redirector;
        $this->params = $params;
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
        return $this->redirector->getRedirectResponse($this->params->get('redirectTarget'));
    }
}
