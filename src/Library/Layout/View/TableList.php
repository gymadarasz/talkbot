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
 * TableList
 *
 * @category  PHP
 * @package   Madsoft\Library\Layout\View
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */
class TableList
{
    const TPL_PATH = __DIR__ . '/../../phtml/';
    
    protected Params $params;
    protected Template $template;
    
    /**
     * Method __construct
     *
     * @param Params   $params   params
     * @param Template $template template
     */
    public function __construct(Params $params, Template $template)
    {
        $this->params = $params;
        $this->template = $template;
    }
    
    /**
     * Method getList
     *
     * @return string
     *
     * @suppress PhanUnreferencedPublicMethod
     */
    public function getList(): string
    {
        return $this->template->setEncoder(null)->process(
            'table-list.phtml',
            $this->params->get('table-list'),
            $this::TPL_PATH
        );
    }
}
