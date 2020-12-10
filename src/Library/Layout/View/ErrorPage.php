<?php declare(strict_types = 1);

/**
 * PHP version 7.4
 *
 * @category  PHP
 * @package   Madsoft\Library\Layout\View
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */

namespace Madsoft\Library\Layout\View;

use Madsoft\Library\Template;

/**
 * Error
 *
 * @category  PHP
 * @package   Madsoft\Library\Layout\View
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */
class ErrorPage
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
     * Method getErrorContent
     *
     * @return string
     */
    public function getErrorContent(): string
    {
        return $this->template->setEncoder(null)->process(
            'error.phtml',
            [],
            $this::TPL_PATH
        );
    }
}
