<?php declare(strict_types = 1);

/**
 * PHP version 7.4
 *
 * @category  PHP
 * @package   Madsoft\Library\App
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */

namespace Madsoft\Library\App;

use Madsoft\Library\Json;
use Madsoft\Library\Router;

/**
 * ApiApp
 *
 * @category  PHP
 * @package   Madsoft\Library\App
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */
class ApiApp extends WebApp
{
    /**
     * Method getOutput
     *
     * @return string
     */
    public function getOutput(): string
    {
        return $this->invoker->getInstance(Json::class)->encode(
            ($this->invoker->getInstance(Router::class))
                ->getArrayResponse($this->routes, 'api')
        );
    }
    
    /**
     * Method getRouteCacheFile
     *
     * @return string
     */
    public function getRouteCacheFile(): string
    {
        return $this->routeCache->getRouteCacheFile('api');
    }
    
    /**
     * Method run
     *
     * @return App
     */
    public function run(): App
    {
        header('content-type: application/json');
        echo $this->getOutput();
        return $this;
    }
}
