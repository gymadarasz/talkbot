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

use Madsoft\Library\Router;
use Madsoft\Library\Server;

/**
 * WebApp
 *
 * @category  PHP
 * @package   Madsoft\Library\App
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */
class WebApp extends App
{
    /**
     * Variable $routes
     *
     * @var mixed[]
     */
    protected array $routes;
    
    /**
     * Method setRoutes
     *
     * @param mixed[] $routes routes
     *
     * @return self
     */
    public function setRoutes(array $routes): self
    {
        $this->routes = $routes;
        return $this;
    }
    
    /**
     * Method getOutput
     *
     * @return string
     */
    public function getOutput(): string
    {
        $router = $this->invoker->getInstance(Router::class);
        $server = $this->invoker->getInstance(Server::class);
        if ('GET' === $server->getMethod()) {
            $router->setCsrfCheck(false);
        }
        return $router->getStringResponse($this->routes);
    }

    /**
     * Method run
     *
     * @return App
     */
    public function run(): App
    {
        header('content-type: text/html');
        echo $this->getOutput();
        return $this;
    }
}
