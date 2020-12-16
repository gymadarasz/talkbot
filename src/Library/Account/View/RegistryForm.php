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
 * RegistryForm
 *
 * @category  PHP
 * @package   Madsoft\Library\Account\View
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */
class RegistryForm
{
    const TPL_PATH = __DIR__ . '/../../phtml/account/';
    
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
     * Method getRegistryForm
     *
     * @return string
     *
     * @suppress PhanUnreferencedPublicMethod
     */
    public function getRegistryForm(): string
    {
        return $this->template->setEncoder(null)->process(
            'registry.phtml',
            [],
            $this::TPL_PATH
        );
    }
}
