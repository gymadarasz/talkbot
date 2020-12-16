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

use Madsoft\Library\Params;
use Madsoft\Library\Template;

/**
 * Header
 *
 * @category  PHP
 * @package   Madsoft\Library\Layout\View
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */
class Header
{
    const TPL_PATH = __DIR__ . '/../../phtml/';
    
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
     * Method getHeader
     *
     * @return string
     */
    public function getHeader(): string
    {
        return $this->template->setEncoder(null)->process(
            'header.phtml',
            ['header' => $this->params->get('header')],
            $this::TPL_PATH
        );
    }
}
