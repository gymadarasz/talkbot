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

use Exception;
use Madsoft\Library\Responder\ArrayResponder;
use Madsoft\Library\Validator\Validator;
use RuntimeException;
use function count;

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
    const ERR_EXCEPTION = 'Hoops! Something went wrong..';
            
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
        try {
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

            if (isset($target['overrides'])) {
                $this->params->setOverrides($target['overrides']);
                unset($target['overrides']);
            }
            if (isset($target['defaults'])) {
                $this->params->setDefaults($target['defaults']);
                unset($target['defaults']);
            }
            if (isset($target['validations'])) {
                $errors = $this->getValidationErrors($target['validations']);
                if ($errors) {
                    return $this->invoker
                        ->getInstance(ArrayResponder::class)
                        ->getErrorResponse(self::ERR_INVALID, $errors);
                }
                unset($target['validations']);
            }
            $this->validateTargetKeys($target, $area, $method, $route);
            return $this->invoker->invoke(
                [
                    'class' => $target['class'],
                    'method' => $target['method']
                ]
            );
        } catch (Exception $exception) {
            $this->invoker->getInstance(Logger::class)->exception($exception);
        }
        return $this->invoker
            ->getInstance(ArrayResponder::class)
            ->getErrorResponse(self::ERR_EXCEPTION);
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
     * @param string[] $target target
     * @param string   $area   area
     * @param string   $method method
     * @param string   $route  route
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
     * Method validateTargetKeys
     *
     * @param string[] $target target
     * @param string   $area   area
     * @param string   $method method
     * @param string   $route  route
     *
     * @return void
     * @throws RuntimeException
     */
    protected function validateTargetKeys(
        array $target,
        string $area,
        string $method,
        string $route
    ): void {
        if (['class', 'method'] !== array_keys($target)) {
            unset($target['class']);
            unset($target['method']);
            throw new RuntimeException(
                'Invalid routing target keys: "'
                    . implode('", "', array_keys($target)) . '" at '
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
            if (!is_array($validation)) {
                throw new RuntimeException(
                    'Validation should be an array: "' . $validation . '" given, '
                        . 'current validations: "'
                        . implode('", "', array_keys($validations)) . '"'
                );
            }
            if (!isset($validation['value'])) {
                throw new RuntimeException(
                    'Key "value" is missing from validation, '
                        . 'current keys: "'
                        . implode('", "', array_keys($validation)) . '", '
                        . 'current validations: "'
                        . implode('", "', array_keys($validations)) . '"'
                );
            }
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
            '/\{\{\s*([a-zA-Z0-9_\-\.]*)\s*\}\}/',
            $value,
            $matches
        )
        ) {
            foreach ($matches[1] as $key => $match) {
                $splits = explode('.', $match);
                $count = count($splits);
                if ($count === 1) {
                    $value = str_replace(
                        $matches[0][$key],
                        $this->params->get($match),
                        $value
                    );
                    continue;
                }
                if ($count === 2) {
                    $field = $this->params->get($splits[0]);
                    if (!is_array($field)) {
                        throw new RuntimeException(
                            'Incorrect parameter validation field: "'
                                . $splits[0] . '" expected to be an array, '
                                . $field . ' given.'
                        );
                    }
                    if (!isset($field[$splits[1]])) {
                        throw new RuntimeException(
                            'Missing parameter field for validation: "'
                                . $match . '"'
                        );
                    }
                    $value = str_replace(
                        $matches[0][$key],
                        $field[$splits[1]],
                        $value
                    );
                    continue;
                }
                throw new RuntimeException(
                    'Incorrect parameter validation value: "'
                        . $match . '"'
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
