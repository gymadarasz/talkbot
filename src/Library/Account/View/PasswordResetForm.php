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

use Madsoft\Library\FileCollector;
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
    const JS_PATH = __DIR__ . '/js/';
    
    protected Template $template;
    protected FileCollector $fileCollector;
    
    /**
     * Method __construct
     *
     * @param Template      $template      template
     * @param FileCollector $fileCollector fileCollector
     */
    public function __construct(Template $template, FileCollector $fileCollector)
    {
        $this->template = $template;
        $this->fileCollector = $fileCollector;
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
        $this->fileCollector->addJsFile($this::JS_PATH . 'account.js');
        return $this->template->setEncoder(null)->process(
            'password-reset.phtml',
            [],
            $this::TPL_PATH
        );
    }
}
