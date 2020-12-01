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

use Madsoft\Library\Messages;
use Madsoft\Library\Responder\ArrayResponder;
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
     * @param User     $user     user
     */
    public function __construct(
        Messages $messages,
        User $user
    ) {
        parent::__construct($messages);
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
