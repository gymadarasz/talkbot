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
use Madsoft\Library\Layout\Layout;
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
 *
 * @todo remove suppress warnings:
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Router
{
    const OK = '';
    const ERR_NOT_FOUND = 'Requested page is not found..';
    const ERR_INVALID = 'Invalid parameter(s)';
    const ERR_EXCEPTION = 'Whoops! Something went wrong..';
    
            
    const ROUTE_QUERY_KEY = 'q';

    protected bool $csrfCheck = true;
    protected string $error = self::OK;
    protected ?string $route = null;
    
    protected Invoker $invoker;
    protected Server $server;
    protected Params $params;
    protected Session $session;
    protected User $user;
    protected Replacer $replacer;
    protected RouteCache $routeCache;
    
    /**
     * Method __construct
     *
     * @param Invoker    $invoker    invoker
     * @param Server     $server     server
     * @param Params     $params     params
     * @param Session    $session    session
     * @param User       $user       user
     * @param Replacer   $replacer   replacer
     * @param RouteCache $routeCache routeCache
     */
    public function __construct(
        Invoker $invoker,
        Server $server,
        Params $params,
        Session $session,
        User $user,
        Replacer $replacer,
        RouteCache $routeCache
    ) {
        $this->invoker = $invoker;
        $this->server = $server;
        $this->params = $params;
        $this->session = $session;
        $this->user = $user;
        $this->replacer = $replacer;
        $this->routeCache = $routeCache;
    }
    
    /**
     * Method setCsrfCheck
     *
     * @param bool $csrfCheck csrfCheck
     *
     * @return self
     */
    public function setCsrfCheck(bool $csrfCheck): self
    {
        $this->csrfCheck = $csrfCheck;
        return $this;
    }
    
    /**
     * Method getStringResponse
     *
     * @param mixed[] $routes               routes
     * @param string  $routeCacheFilePrefix routeCacheFilePrefix
     *
     * @return string
     */
    public function getStringResponse(
        array $routes,
        string $routeCacheFilePrefix
    ): string {
        $response = $this->getArrayResponse($routes, $routeCacheFilePrefix);
        return $this->invoker->getInstance(Layout::class)->getHtmlPage(
            $response,
            $this->error
        );
    }
    
    /**
     * Method routing
     *
     * @param mixed[] $routes               routes
     * @param string  $routeCacheFilePrefix routeCacheFilePrefix
     *
     * @return mixed[]
     */
    public function getArrayResponse(
        array $routes,
        string $routeCacheFilePrefix
    ): array {
        try {
            $this->params->sanitizeSql();
            
            $route = $this->getRoute();

            if ($this->csrfCheck) {
                $csrf = $this->invoker->getInstance(Csrf::class);
                if ($route === 'csrf') {
                    return $csrf->getAsArray();
                }
                $csrf->check();
            }
            
            $area = $this->getRoutingArea();
            $this->validateArea(
                $routes,
                $area,
                $route,
                $routeCacheFilePrefix
            );
            $method = $this->server->getMethod();
            $this->validateMethod(
                $routes,
                $area,
                $method,
                $route,
                $routeCacheFilePrefix
            );
        
            $target = $this->resolveTarget(
                $routes,
                $area,
                $method,
                $route,
                $routeCacheFilePrefix
            );
            
            if (array_key_exists('validations', $target)) {
                $errors = $this->getValidationErrors(
                    $this->replacer->replaceAll(
                        $target['validations'],
                        $this->getReplacerAssocs()
                    )
                );
                if ($errors) {
                    $this->error = self::ERR_INVALID;
                    return $this->invoker
                        ->getInstance(ArrayResponder::class)
                        ->getErrorResponse($this->error, $errors);
                }
                unset($target['validations']);
            }
            $this->validateTargetKeys(
                $target,
                $area,
                $method,
                $route,
                $routeCacheFilePrefix
            );
            
            return $this->invoker->invoke(
                [
                    'class' => $target['class'],
                    'method' => $target['method']
                ]
            );
        } catch (Exception $exception) {
            $this->invoker->getInstance(Logger::class)->exception($exception);
            if ('test' === $this->invoker->getInstance(Config::class)->getEnv()) {
                $this->invoker->getInstance(Throwier::class)->forward($exception);
            }
        }
        if (!$this->error) {
            $this->error = self::ERR_EXCEPTION;
        }
        return $this->invoker
            ->getInstance(ArrayResponder::class)
            ->getErrorResponse($this->error);
    }

    /**
     * Method getRoute
     *
     * @return string
     */
    public function getRoute(): string
    {
        if (null === $this->route) {
            $route = '';
            if ($this->params->has(self::ROUTE_QUERY_KEY)) {
                $route = $this->params->get(self::ROUTE_QUERY_KEY);
            }
            if ($route === '/') {
                $route = '';
            }
            $this->route = $route;
        }
        return $this->route;
    }
    
    /**
     * Method resolveTarget
     *
     * @param mixed[] $routes               routes
     * @param string  $area                 area
     * @param string  $method               method
     * @param string  $route                route
     * @param string  $routeCacheFilePrefix routeCacheFilePrefix
     *
     * @return mixed[]
     */
    protected function resolveTarget(
        array $routes,
        string $area,
        string $method,
        string $route,
        string $routeCacheFilePrefix
    ): array {
        $this->validateRoute(
            $routes,
            $area,
            $method,
            $route,
            $routeCacheFilePrefix
        );
        $target = $routes[$area][$method][$route];
        $this->validateTarget(
            $target,
            $area,
            $method,
            $route,
            $routeCacheFilePrefix
        );

        if (array_key_exists('defaults', $target)) {
            $this->params->setDefaults(
                $this->replacer->replaceAll(
                    $target['defaults'],
                    $this->getReplacerAssocs()
                )
            );
            unset($target['defaults']);
        }
            
        $this->params->setOverrides(
            $this->replacer->replaceAll(
                $this->addTargetOverrides($target)['overrides'],
                $this->getReplacerAssocs()
            )
        );
        unset($target['overrides']);
        return $target;
    }


    /**
     * Method addTargetOverrides
     *
     * @param mixed[] $target target
     *
     * @return mixed[]
     */
    protected function addTargetOverrides(array $target): array
    {
        if (!array_key_exists('overrides', $target)) {
            $target['overrides'] = [
                    'join' => '',
                    'where' => '',
                ];
        }
        if (!array_key_exists('join', $target['overrides'])) {
            $target['overrides']['join'] = '';
        }
        if (!array_key_exists('where', $target['overrides'])) {
            $target['overrides']['where'] = '';
        }
        return $target;
    }
    
    /**
     * Method validateArea
     *
     * @param mixed[] $routes               routes
     * @param string  $area                 area
     * @param string  $route                route
     * @param string  $routeCacheFilePrefix routeCacheFilePrefix
     *
     * @return void
     * @throws RuntimeException
     */
    protected function validateArea(
        array $routes,
        string $area,
        string $route,
        string $routeCacheFilePrefix
    ): void {
        if (!array_key_exists($area, $routes)) {
            throw new RuntimeException(
                'Requested area has not any routing point: ' . $area . ' ?'
                    . self::ROUTE_QUERY_KEY . '=' . $route .
                    ' (did you try to delete "'
                    . $this->routeCache->getRouteCacheFile($routeCacheFilePrefix)
                    . '"?)'
            );
        }
    }
    
    /**
     * Method validateMethod
     *
     * @param mixed[] $routes               routes
     * @param string  $area                 area
     * @param string  $method               method
     * @param string  $route                route
     * @param string  $routeCacheFilePrefix routeCacheFilePrefix
     *
     * @return void
     * @throws RuntimeException
     */
    protected function validateMethod(
        array $routes,
        string $area,
        string $method,
        string $route,
        string $routeCacheFilePrefix
    ): void {
        if (!array_key_exists($method, $routes[$area])) {
            throw new RuntimeException(
                'Route is not defined for method on area: '
                    . 'routes[' . $area . '][' . $method . '] ?'
                    . self::ROUTE_QUERY_KEY . '=' . $route .
                    ' (did you try to delete "'
                    . $this->routeCache->getRouteCacheFile($routeCacheFilePrefix)
                    . '"?)'
            );
        }
    }
    
    /**
     * Method validateRoute
     *
     * @param mixed[] $routes               routes
     * @param string  $area                 area
     * @param string  $method               method
     * @param string  $route                route
     * @param string  $routeCacheFilePrefix routeCacheFilePrefix
     *
     * @return void
     * @throws RuntimeException
     */
    protected function validateRoute(
        array $routes,
        string $area,
        string $method,
        string $route,
        string $routeCacheFilePrefix
    ): void {
        if (!array_key_exists($route, $routes[$area][$method])) {
            $this->error = self::ERR_NOT_FOUND;
            throw new RuntimeException(
                'Route is not defined on area for query: '
                    . 'routes[' . $area . '][' . $method . '] ?'
                    . self::ROUTE_QUERY_KEY . '=' . $route .
                    ' (did you try to delete "'
                    . $this->routeCache->getRouteCacheFile($routeCacheFilePrefix)
                    . '"?)'
            );
        }
    }
    
    /**
     * Method validateTarget
     *
     * @param string[] $target               target
     * @param string   $area                 area
     * @param string   $method               method
     * @param string   $route                route
     * @param string   $routeCacheFilePrefix routeCacheFilePrefix
     *
     * @return void
     * @throws RuntimeException
     */
    protected function validateTarget(
        array $target,
        string $area,
        string $method,
        string $route,
        string $routeCacheFilePrefix
    ): void {
        if (!array_key_exists('class', $target)) {
            throw new RuntimeException(
                'Class is not defined at routing point: '
                    . "routes[$area][$method][$route][class] => ??? ?"
                    . self::ROUTE_QUERY_KEY . '=' . $route .
                    ' (did you try to delete "'
                    . $this->routeCache->getRouteCacheFile($routeCacheFilePrefix)
                    . '"?)'
            );
        }
        if (!array_key_exists('method', $target)) {
            $class = $target['class'];
            throw new RuntimeException(
                "Method is not defined at routing point for class $class::???() "
                    . "routes[$area][$method][$route][method] => ??? ?"
                    . self::ROUTE_QUERY_KEY . '=' . $route .
                    ' (did you try to delete "'
                    . $this->routeCache->getRouteCacheFile($routeCacheFilePrefix)
                    . '"?)'
            );
        }
    }
    
    /**
     * Method validateTargetKeys
     *
     * @param string[] $target               target
     * @param string   $area                 area
     * @param string   $method               method
     * @param string   $route                route
     * @param string   $routeCacheFilePrefix routeCacheFilePrefix
     *
     * @return void
     * @throws RuntimeException
     */
    protected function validateTargetKeys(
        array $target,
        string $area,
        string $method,
        string $route,
        string $routeCacheFilePrefix
    ): void {
        if (['class', 'method'] !== array_keys($target)) {
            unset($target['class']);
            unset($target['method']);
            throw new RuntimeException(
                'Invalid routing target keys: "'
                    . implode('", "', array_keys($target)) . '" at '
                    . "routes[$area][$method][$route][method] => ??? ?"
                    . self::ROUTE_QUERY_KEY . '=' . $route .
                    ' (did you try to delete "'
                    . $this->routeCache->getRouteCacheFile($routeCacheFilePrefix)
                    . '"?)'
            );
        }
    }
    
    /**
     * Method getRoutingArea
     *
     * @return string
     */
    public function getRoutingArea(): string
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
            if (!array_key_exists('value', $validation)) {
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
        return $this->replacer->replace(
            $value,
            $this->getReplacerAssocs()
        );
    }
    
    /**
     * Method getReplacerAssocs
     *
     * @return Assoc[]
     */
    protected function getReplacerAssocs(): array
    {
        return [
            'params' => $this->params,
            'session' => $this->session,
        ];
    }
}
