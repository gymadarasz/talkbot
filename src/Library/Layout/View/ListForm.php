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

/**
 * ListForm
 *
 * @category  PHP
 * @package   Madsoft\Library\Layout\View
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */
class ListForm
{
    protected Params $params;
    
    /**
     * Method __construct
     *
     * @param Params $params params
     */
    public function __construct(Params $params)
    {
        $this->params = $params;
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
        $api = $this->params->get('list-form')['api'];
        return '[LIST FORM HERE, api parameter: "' . $api . '"]';
    }
}
