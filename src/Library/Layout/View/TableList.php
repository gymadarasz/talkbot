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

use Madsoft\Library\Json;
use Madsoft\Library\Params;
use Madsoft\Library\Template;
use RuntimeException;

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
    protected Json $json;


    /**
     * Method __construct
     *
     * @param Params   $params   params
     * @param Template $template template
     * @param Json     $json     json
     */
    public function __construct(Params $params, Template $template, Json $json)
    {
        $this->params = $params;
        $this->template = $template;
        $this->json = $json;
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
        $params = $this->params->get('table-list');
        foreach ($params['columns'] as &$column) {
            if (!array_key_exists('actions', $column)) {
                throw new RuntimeException('Column action is not defined for list');
            }
            $column['actions'] = $column['actions'] ?
                    htmlentities($this->json->encode($column['actions'])) : '';
        }
        return $this->template->setEncoder(null)->process(
            'table-list.phtml',
            $params,
            $this::TPL_PATH
        );
    }
}
