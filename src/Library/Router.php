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

use Madsoft\Library\Responder\ArrayResponder;
use Madsoft\Library\Validator\Validator;
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
    const ERR_INVALID = 'Invalid parameter(s)';
            
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
     * @param mixed[] $routes routes
     *
     * @return mixed[]
     */
    public function routing(array $routes): array
    {
        $route = $this->params->get(self::ROUTE_QUERY_KEY, '');
        
        if ($route === 'csrf') {
            return $this->invoker->getInstance(Csrf::class)->getAsArray();
        }
        $this->invoker->getInstance(Csrf::class)->check();
        
        $area = $this->getRoutingArea();
        $this->validateArea($routes, $area, $route);
        $method = $this->server->getMethod();
        $this->validateMethod($routes, $area, $method, $route);
        $this->validateRoute($routes, $area, $method, $route);
        $target = $routes[$area][$method][$route];
        $this->validateTarget($target, $area, $method, $route);
        
        if (isset($target['defaults'])) {
            $this->params->setDefaults($target['defaults']);
        }
        if (isset($target['validations'])) {
            $errors = $this->getValidationErrors($target['validations']);
            if ($errors) {
                return $this->invoker
                    ->getInstance(ArrayResponder::class)
                    ->getErrorResponse(self::ERR_INVALID, $errors);
            }
        }
        return $this->invoker->invoke(
            [
                'class' => $target['class'],
                'method' => $target['method']
            ]
        );
    }
    
    /**
     * Method validateArea
     *
     * @param mixed[] $routes routes
     * @param string  $area   area
     * @param string  $route  route
     *
     * @return void
     * @throws RuntimeException
     */
    protected function validateArea(
        array $routes,
        string $area,
        string $route
    ): void {
        if (!isset($routes[$area])) {
            throw new RuntimeException(
                'Requested area has not any routing point: ' . $area . ' ?'
                    . self::ROUTE_QUERY_KEY . '=' . $route .
                    ' (did you try to delete "' . self::ROUTE_CACHE_FILE . '"?)'
            );
        }
    }
    
    /**
     * Method validateMethod
     *
     * @param mixed[] $routes routes
     * @param string  $area   area
     * @param string  $method method
     * @param string  $route  route
     *
     * @return void
     * @throws RuntimeException
     */
    protected function validateMethod(
        array $routes,
        string $area,
        string $method,
        string $route
    ): void {
        if (!isset($routes[$area][$method])) {
            throw new RuntimeException(
                'Route is not defined for method on area: '
                    . 'routes[' . $area . '][' . $method . '] ?'
                    . self::ROUTE_QUERY_KEY . '=' . $route .
                    ' (did you try to delete "' . self::ROUTE_CACHE_FILE . '"?)'
            );
        }
    }
    
    /**
     * Method validateRoute
     *
     * @param mixed[] $routes routes
     * @param string  $area   area
     * @param string  $method method
     * @param string  $route  route
     *
     * @return void
     * @throws RuntimeException
     */
    protected function validateRoute(
        array $routes,
        string $area,
        string $method,
        string $route
    ): void {
        if (!isset($routes[$area][$method][$route])) {
            throw new RuntimeException(
                'Route is not defined on area for query: '
                    . 'routes[' . $area . '][' . $method . '] ?'
                    . self::ROUTE_QUERY_KEY . '=' . $route .
                    ' (did you try to delete "' . self::ROUTE_CACHE_FILE . '"?)'
            );
        }
    }
    
    /**
     * Method validateTarget
     *
     * @param mixed[] $target target
     * @param string  $area   area
     * @param string  $method method
     * @param string  $route  route
     *
     * @return void
     * @throws RuntimeException
     */
    protected function validateTarget(
        array $target,
        string $area,
        string $method,
        string $route
    ): void {
        if (!isset($target['class'])) {
            throw new RuntimeException(
                'Class is not defined at routing point: '
                    . "routes[$area][$method][$route][class] => ??? ?"
                    . self::ROUTE_QUERY_KEY . '=' . $route .
                    ' (did you try to delete "' . self::ROUTE_CACHE_FILE . '"?)'
            );
        }
        if (!isset($target['method'])) {
            $class = $target['class'];
            throw new RuntimeException(
                "Method is not defined at routing point for class $class::???() "
                    . "routes[$area][$method][$route][method] => ??? ?"
                    . self::ROUTE_QUERY_KEY . '=' . $route .
                    ' (did you try to delete "' . self::ROUTE_CACHE_FILE . '"?)'
            );
        }
    }
    
    /**
     * Method getRoutingArea
     *
     * @return string
     */
    protected function getRoutingArea(): string
    {
        $area = 'public';
        if (!$this->user->isVisitor()) {
            $area = 'protected';
            if ($this->user->isAdmin()) {
                $area = 'private';
            }
        }
        return $area;
    }
    
    /**
     * Method getValidationErrors
     *
     * @param string[][] $validations validations
     *
     * @return string[][]
     */
    protected function getValidationErrors(array $validations): array
    {
        foreach ($validations as &$validation) {
            $validation['value'] = $this->getValidationValue(
                $validation['value']
            );
        }
        $errors = $this->invoker
            ->getInstance(Validator::class)
            ->getErrors($validations);
        return $errors;
    }
    
    /**
     * Method getValidationValue
     *
     * @param string $value value
     *
     * @return string
     */
    protected function getValidationValue(string $value): string
    {
        $matches = null;
        if (preg_match_all(
            '/\{\{\s*([a-zA-Z0-9_]*)\s*\}\}/',
            $value,
            $matches
        )
        ) {
            foreach ($matches[1] as $key => $match) {
                $value = str_replace(
                    $matches[0][$key],
                    $this->params->get($match),
                    $value
                );
            }
        }
        return $value;
    }
    
    /**
     * Method loadRoutes
     *
     * @param string[] $includes includes
     *
     * @return mixed[]
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
     * @return mixed[]
     */
    protected function includeRoutes(string $include)
    {
        $routes = [];
        include $include;
        return $routes;
    }
}
