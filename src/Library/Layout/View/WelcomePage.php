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

use Madsoft\Library\Router;

/**
 * WelcomePage
 *
 * @category  PHP
 * @package   Madsoft\Library\Layout\View
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */
class WelcomePage
{
    protected Router $router;
    
    /**
     * Method __construct
     *
     * @param Router $router router
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }
    /**
     * Method getPublicArea
     *
     * @return string
     *
     * @suppress PhanUnreferencedPublicMethod
     */
    public function getPublicArea(): string
    {
        $area = $this->router->getRoutingArea();
        return '[WELCOME PAGE HERE... (routing area is "' . $area . '")]';
    }
}
