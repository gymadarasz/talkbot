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
use Madsoft\Library\Template;

/**
 * ContentPage
 *
 * @category  PHP
 * @package   Madsoft\Library\Layout\View
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */
class ContentPage
{
    const TPL_PATH = __DIR__ . '/../../phtml/';
    
    protected Template $template;
    protected Content $content;
    
    /**
     * Method __construct
     *
     * @param Template $template template
     * @param Content  $content  content
     */
    public function __construct(
        Template $template,
        Content $content
    ) {
        $this->template = $template;
        $this->content = $content;
    }
    
    /**
     * Method getContent
     *
     * @return string
     *
     * @suppress PhanUnreferencedPublicMethod
     */
    public function getContent(): string
    {
        return $this->template->setEncoder(null)->process(
            'content.phtml',
            $this->content->getRow(),
            $this::TPL_PATH
        );
    }
}
