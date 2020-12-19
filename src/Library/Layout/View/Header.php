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

use Madsoft\Library\Content;
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
    protected Content $content;
    
    /**
     * Method __construct
     *
     * @param Template $template template
     * @param Params   $params   params
     * @param Content  $content  content
     */
    public function __construct(
        Template $template,
        Params $params,
        Content $content
    ) {
        $this->template = $template;
        $this->params = $params;
        $this->content = $content;
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
            ['header' => $this->getHeaderContent()],
            $this::TPL_PATH
        );
    }
    
    /**
     * Method getHeaderContent
     *
     * @return string
     */
    protected function getHeaderContent(): string
    {
        if (null !== $this->content->getContentId()) {
            return $this->content->get('header');
        }
        return $this->params->get('header');
    }
}
