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
use Madsoft\Library\User;

/**
 * Logout
 *
 * @category  PHP
 * @package   Madsoft\Library\Account
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */
class Logout extends ArrayResponder
{
    protected User $user;
    
    /**
     * Method __construct
     *
     * @param Messages $messages messages
     * @param Csrf     $csrf     csrf
     * @param Session  $session  session
     * @param User     $user     user
     */
    public function __construct(
        Messages $messages,
        Csrf $csrf,
        Session $session,
        User $user
    ) {
        parent::__construct($messages, $csrf, $session);
        $this->user = $user;
    }

    /**
     * Method getLogoutResponse
     *
     * @return mixed[]
     *
     * @suppress PhanUnreferencedPublicMethod
     */
    public function getLogoutResponse(): array
    {
        $this->user->logout();
        
        return $this->getSuccessResponse(
            'Logout success'
        );
    }
}
