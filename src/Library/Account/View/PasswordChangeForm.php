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
use Madsoft\Library\Template;

/**
 * PasswordChangeForm
 *
 * @category  PHP
 * @package   Madsoft\Library\Account\View
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */
class PasswordChangeForm
{
    const TPL_PATH = __DIR__ . '/../../phtml/account/';
    
    protected Template $template;
    protected Params $params;
    
    /**
     * Method __construct
     *
     * @param Template $template template
     * @param Params   $params   params
     */
    public function __construct(Template $template, Params $params)
    {
        $this->template = $template;
        $this->params = $params;
    }
    
    /**
     * Method getPasswordChangeForm
     *
     * @return string
     *
     * @suppress PhanUnreferencedPublicMethod
     */
    public function getPasswordChangeForm(): string
    {
        return $this->template->setEncoder(null)->process(
            'password-change.phtml',
            ['token' => $this->params->get('token')],
            $this::TPL_PATH
        );
    }
}
