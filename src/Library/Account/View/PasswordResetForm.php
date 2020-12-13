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

/**
 * PasswordResetForm
 *
 * @category  PHP
 * @package   Madsoft\Library\Account\View
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */
class PasswordResetForm
{
    const TPL_PATH = __DIR__ . '/phtml/';
    
    protected Template $template;
    
    /**
     * Method __construct
     *
     * @param Template $template template
     */
    public function __construct(Template $template)
    {
        $this->template = $template;
    }
    
    /**
     * Method getPasswordResetForm
     *
     * @return string
     *
     * @suppress PhanUnreferencedPublicMethod
     */
    public function getPasswordResetForm(): string
    {
        return $this->template->setEncoder(null)->process(
            'password-reset.phtml',
            [],
            $this::TPL_PATH
        );
    }
}
