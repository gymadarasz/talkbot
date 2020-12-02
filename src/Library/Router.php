<?php declare(strict_types = 1);

/**
 * PHP version 7.4
 *
 * @category  PHP
 * @package   Madsoft\Library
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */

namespace Madsoft\Library;

use RuntimeException;

/**
 * Router
 *
 * @category  PHP
 * @package   Madsoft\Library
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */
class Router
{
    const ROUTE_QUERY_KEY = 'q';
    const ROUTE_CACHE_FILE = __DIR__ . '/../../routes.cache.php';
    
    protected Invoker $invoker;
    protected Server $server;
    protected Params $params;
    protected User $user;
    protected Merger $merger;

    /**
     * Method __construct
     *
     * @param Invoker $invoker invoker
     * @param Server  $server  server
     * @param Params  $params  params
     * @param User    $user    user
     * @param Merger  $merger  merger
     */
    public function __construct(
        Invoker $invoker,
        Server $server,
        Params $params,
        User $user,
        Merger $merger
    ) {
        $this->invoker = $invoker;
        $this->server = $server;
        $this->params = $params;
        $this->user = $user;
        $this->merger = $merger;
    }
    
    /**
     * Method routing
     *
     * @param string[][][][] $routes routes
     *
     * @return mixed[]
     */
    public function routing(array $routes): array
    {
        $area = 'public';
        if (!$this->user->isVisitor()) {
            $area = 'protected';
            if ($this->user->isAdmin()) {
                $area = 'private';
            }
        }
        if (!isset($routes[$area])) {
            throw new RuntimeException(
                'Requested area has not any routing point: ' . $area
            );
        }
        $method = $this->server->getMethod();
        if (!isset($routes[$area][$method])) {
            throw new RuntimeException(
                'Route is not defined for method on area: '
                    . 'routes[' . $area . '][' . $method . ']'
            );
        }
        $route = $this->params->get(self::ROUTE_QUERY_KEY, '');
        if (!isset($routes[$area][$method][$route])) {
            throw new RuntimeException(
                'Route is not defined on area for query: '
                . 'routes[' . $area . '][' . $method . '] ?'
                . self::ROUTE_QUERY_KEY . '=' . $route
            );
        }
        $target = $routes[$area][$method][$route];
        if (!isset($target['class'])) {
            throw new RuntimeException(
                'Class is not defined at routing point: ' .
                "routes[$area][$method][$route][class] => ???"
            );
        }
        if (!isset($target['method'])) {
            $class = $target['class'];
            throw new RuntimeException(
                "Method is not defined at routing point for class $class::???() " .
                "routes[$area][$method][$route][method] => ???"
            );
        }
        return $this->invoker->invoke($target);
    }
    
    /**
     * Method loadRoutes
     *
     * @param string[] $includes includes
     *
     * @return string[][][][]
     */
    public function loadRoutes(array $includes): array
    {
        if (!file_exists(self::ROUTE_CACHE_FILE)) {
            $routes = [];
            foreach ($includes as $include) {
                $routes = $this->merger->merge(
                    $routes,
                    $this->includeRoutes($include)
                );
            }
            $exported = '<?php $routes = ' . var_export($routes, true) . ';';
            if (!$exported) {
                throw new RuntimeException('Unable to export routes');
            }
            if (false === file_put_contents(self::ROUTE_CACHE_FILE, $exported)) {
                throw new RuntimeException(
                    'Unable to write route cache: ' . self::ROUTE_CACHE_FILE
                );
            }
            clearstatcache(true, self::ROUTE_CACHE_FILE);
        }
        return $this->includeRoutes(self::ROUTE_CACHE_FILE);
    }
    
    /**
     * Method includeRoutes
     *
     * @param string $include include
     *
     * @return string[][][][]
     */
    protected function includeRoutes(string $include)
    {
        $routes = [];
        include $include;
        return $routes;
    }
}
