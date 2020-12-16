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

use Madsoft\Library\Template;
use Madsoft\Library\User;

/**
 * LoginForm
 *
 * @category  PHP
 * @package   Madsoft\Library\Account\View
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */
class LoginForm
{
    const TPL_PATH = __DIR__ . '/../../phtml/account/';
    
    protected Template $template;
    
    /**
     * Method __construct
     *
     * @param Template $template template
     * @param User     $user     user
     */
    public function __construct(
        Template $template,
        User $user
    ) {
        $this->template = $template;
        
        $user->logout();
    }
    
    /**
     * Method getLoginForm
     *
     * @return string
     *
     * @suppress PhanUnreferencedPublicMethod
     */
    public function getLoginForm(): string
    {
        //        $this->fileCollector->addJsFile($this::JS_PATH . 'login.js');
        return $this->template->setEncoder(null)->process(
            'login.phtml',
            [],
            $this::TPL_PATH
        );
    }
}
