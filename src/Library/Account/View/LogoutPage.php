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

use Madsoft\Library\Params;
use Madsoft\Library\Redirector;
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
    protected User $user;
    protected Redirector $redirector;
    protected Params $params;

    /**
     * Method __construct
     *
     * @param User       $user       user
     * @param Redirector $redirector redirector
     * @param Params     $params     params
     */
    public function __construct(
        User $user,
        Redirector $redirector,
        Params $params
    ) {
        $this->user = $user;
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
        return $this->redirector->getRedirectResponse(
            $this->params->get('redirectTarget')
        );
    }
}
