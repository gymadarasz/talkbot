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
 * Api
 *
 * @category  PHP
 * @package   Madsoft\Library\App
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */
class ApiApp extends App
{
    /**
     * Variable $routes
     *
     * @var string[][][][]
     */
    protected array $routes;
    
    /**
     * Method setRoutes
     *
     * @param string[][][][] $routes routes
     *
     * @return self
     */
    public function setRoutes(array $routes): self
    {
        $this->routes = $routes;
        return $this;
    }
    
    /**
     * Method run
     *
     * @return App
     */
    public function run(): App
    {
        header('content-type: application/json');
        echo $this->getOutputJson();
        return $this;
    }
    
    /**
     * Method getOutputJson
     *
     * @return string
     */
    public function getOutputJson(): string
    {
        return $this->invoker->getInstance(Json::class)->encode(
            ($this->invoker->getInstance(Router::class))
                ->routing($this->routes)
        );
    }
}
