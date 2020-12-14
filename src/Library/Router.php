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
    const ERR_INVALID = 'Invalid parameter(s)';
    const ERR_EXCEPTION = 'Whoops! Something went wrong..';
    
            
    const ROUTE_QUERY_KEY = 'q';
    const ROUTE_CACHE_FILEPATH = __DIR__ . '/../../';
    const ROUTE_CACHE_FILENAME = 'routes.cache.php';
    
    protected bool $csrfCheck = true;
    protected string $error = self::OK;
    
    protected Invoker $invoker;
    protected Server $server;
    protected Params $params;
    protected Session $session;
    protected User $user;
    protected Merger $merger;
    protected Replacer $replacer;
    protected Config $config;

    /**
     * Method __construct
     *
     * @param Invoker  $invoker  invoker
     * @param Server   $server   server
     * @param Params   $params   params
     * @param Session  $session  session
     * @param User     $user     user
     * @param Merger   $merger   merger
     * @param Replacer $replacer replacer
     * @param Config   $config   config
     */
    public function __construct(
        Invoker $invoker,
        Server $server,
        Params $params,
        Session $session,
        User $user,
        Merger $merger,
        Replacer $replacer,
        Config $config
    ) {
        $this->invoker = $invoker;
        $this->server = $server;
        $this->params = $params;
        $this->session = $session;
        $this->user = $user;
        $this->merger = $merger;
        $this->replacer = $replacer;
        $this->config = $config;
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
     * @param mixed[] $routes         routes
     * @param string  $routeCacheFile routeCacheFile
     *
     * @return string
     */
    public function getStringResponse(
        array $routes,
        string $routeCacheFile = self::ROUTE_CACHE_FILEPATH
        . self::ROUTE_CACHE_FILENAME
    ): string {
        $response = $this->getArrayResponse($routes, $routeCacheFile);
        return $this->invoker->getInstance(Layout::class)->getHtmlPage(
            $response,
            $this->error
        );
    }
    
    /**
     * Method routing
     *
     * @param mixed[] $routes         routes
     * @param string  $routeCacheFile routeCacheFile
     *
     * @return mixed[]
     */
    public function getArrayResponse(
        array $routes,
        string $routeCacheFile = self::ROUTE_CACHE_FILEPATH
        . self::ROUTE_CACHE_FILENAME
    ): array {
        try {
            $this->params->sanitizeSql();
            
            $route = $this->params->get(self::ROUTE_QUERY_KEY, '');

            if ($this->csrfCheck) {
                $csrf = $this->invoker->getInstance(Csrf::class);
                if ($route === 'csrf') {
                    return $csrf->getAsArray();
                }
                $csrf->check();
            }

            $area = $this->getRoutingArea();
            $this->validateArea($routes, $area, $route, $routeCacheFile);
            $method = $this->server->getMethod();
            $this->validateMethod($routes, $area, $method, $route, $routeCacheFile);
            $this->validateRoute($routes, $area, $method, $route, $routeCacheFile);
            $target = $routes[$area][$method][$route];
            $this->validateTarget($target, $area, $method, $route, $routeCacheFile);

            if (isset($target['defaults'])) {
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
                
            if (isset($target['validations'])) {
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
                $routeCacheFile
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
        $this->error = self::ERR_EXCEPTION;
        return $this->invoker
            ->getInstance(ArrayResponder::class)
            ->getErrorResponse($this->error);
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
        if (!isset($target['overrides'])) {
            $target['overrides'] = [
                    'join' => '',
                    'where' => '',
                ];
        }
        if (!isset($target['overrides']['join'])) {
            $target['overrides']['join'] = '';
        }
        if (!isset($target['overrides']['where'])) {
            $target['overrides']['where'] = '';
        }
        return $target;
    }
    
    /**
     * Method validateArea
     *
     * @param mixed[] $routes         routes
     * @param string  $area           area
     * @param string  $route          route
     * @param string  $routeCacheFile routeCacheFile
     *
     * @return void
     * @throws RuntimeException
     */
    protected function validateArea(
        array $routes,
        string $area,
        string $route,
        string $routeCacheFile = self::ROUTE_CACHE_FILEPATH
        . self::ROUTE_CACHE_FILENAME
    ): void {
        if (!isset($routes[$area])) {
            throw new RuntimeException(
                'Requested area has not any routing point: ' . $area . ' ?'
                    . self::ROUTE_QUERY_KEY . '=' . $route .
                    ' (did you try to delete "' . $routeCacheFile . '"?)'
            );
        }
    }
    
    /**
     * Method validateMethod
     *
     * @param mixed[] $routes         routes
     * @param string  $area           area
     * @param string  $method         method
     * @param string  $route          route
     * @param string  $routeCacheFile routeCacheFile
     *
     * @return void
     * @throws RuntimeException
     */
    protected function validateMethod(
        array $routes,
        string $area,
        string $method,
        string $route,
        string $routeCacheFile = self::ROUTE_CACHE_FILEPATH
        . self::ROUTE_CACHE_FILENAME
    ): void {
        if (!isset($routes[$area][$method])) {
            throw new RuntimeException(
                'Route is not defined for method on area: '
                    . 'routes[' . $area . '][' . $method . '] ?'
                    . self::ROUTE_QUERY_KEY . '=' . $route .
                    ' (did you try to delete "' . $routeCacheFile . '"?)'
            );
        }
    }
    
    /**
     * Method validateRoute
     *
     * @param mixed[] $routes         routes
     * @param string  $area           area
     * @param string  $method         method
     * @param string  $route          route
     * @param string  $routeCacheFile routeCacheFile
     *
     * @return void
     * @throws RuntimeException
     */
    protected function validateRoute(
        array $routes,
        string $area,
        string $method,
        string $route,
        string $routeCacheFile = self::ROUTE_CACHE_FILEPATH
        . self::ROUTE_CACHE_FILENAME
    ): void {
        if (!isset($routes[$area][$method][$route])) {
            throw new RuntimeException(
                'Route is not defined on area for query: '
                    . 'routes[' . $area . '][' . $method . '] ?'
                    . self::ROUTE_QUERY_KEY . '=' . $route .
                    ' (did you try to delete "' . $routeCacheFile . '"?)'
            );
        }
    }
    
    /**
     * Method validateTarget
     *
     * @param string[] $target         target
     * @param string   $area           area
     * @param string   $method         method
     * @param string   $route          route
     * @param string   $routeCacheFile routeCacheFile
     *
     * @return void
     * @throws RuntimeException
     */
    protected function validateTarget(
        array $target,
        string $area,
        string $method,
        string $route,
        string $routeCacheFile = self::ROUTE_CACHE_FILEPATH
        . self::ROUTE_CACHE_FILENAME
    ): void {
        if (!isset($target['class'])) {
            throw new RuntimeException(
                'Class is not defined at routing point: '
                    . "routes[$area][$method][$route][class] => ??? ?"
                    . self::ROUTE_QUERY_KEY . '=' . $route .
                    ' (did you try to delete "' . $routeCacheFile . '"?)'
            );
        }
        if (!isset($target['method'])) {
            $class = $target['class'];
            throw new RuntimeException(
                "Method is not defined at routing point for class $class::???() "
                    . "routes[$area][$method][$route][method] => ??? ?"
                    . self::ROUTE_QUERY_KEY . '=' . $route .
                    ' (did you try to delete "' . $routeCacheFile . '"?)'
            );
        }
    }
    
    /**
     * Method validateTargetKeys
     *
     * @param string[] $target         target
     * @param string   $area           area
     * @param string   $method         method
     * @param string   $route          route
     * @param string   $routeCacheFile routeCacheFile
     *
     * @return void
     * @throws RuntimeException
     */
    protected function validateTargetKeys(
        array $target,
        string $area,
        string $method,
        string $route,
        string $routeCacheFile = self::ROUTE_CACHE_FILEPATH
        . self::ROUTE_CACHE_FILENAME
    ): void {
        if (['class', 'method'] !== array_keys($target)) {
            unset($target['class']);
            unset($target['method']);
            throw new RuntimeException(
                'Invalid routing target keys: "'
                    . implode('", "', array_keys($target)) . '" at '
                    . "routes[$area][$method][$route][method] => ??? ?"
                    . self::ROUTE_QUERY_KEY . '=' . $route .
                    ' (did you try to delete "' . $routeCacheFile . '"?)'
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
    
    /**
     * Method loadRoutes
     *
     * @param string[] $includes             includes
     * @param string   $routeCacheFilePrefix routeCacheFilePrefix
     *
     * @return mixed[]
     */
    public function loadRoutes(
        array $includes,
        string $routeCacheFilePrefix
    ): array {
        $routeCacheFile = self::ROUTE_CACHE_FILEPATH . "$routeCacheFilePrefix."
            . self::ROUTE_CACHE_FILENAME;
        if (!file_exists($routeCacheFile) || $this->config->getEnv() === 'test') {
            $routes = [
                'public' => [],
                'protected' => [],
                'private' => [],
            ];
            $export = null;
            while ($export !== var_export($routes, true)) {
                $export = var_export($routes, true);
                foreach ($includes as $include) {
                    $routes = $this->merger->merge(
                        $routes,
                        $this->includeRoutes($include)
                    );
                }
                $routes['protected'] = $this->merger->merge(
                    $routes['protected'],
                    $routes['public']
                );
                $routes['private'] = $this->merger->merge(
                    $routes['private'],
                    $routes['protected']
                );
                foreach ($includes as $include) {
                    $routes = $this->merger->merge(
                        $routes,
                        $this->includeRoutes($include)
                    );
                }
            }
            $exported = '<?php $routes = ' . $export . ';';
            if (!$exported) {
                throw new RuntimeException('Unable to export routes');
            }
            if (false === file_put_contents($routeCacheFile, $exported)) {
                throw new RuntimeException(
                    'Unable to write route cache: ' . $routeCacheFile
                );
            }
            clearstatcache(true, $routeCacheFile);
        }
        return $this->includeRoutes($routeCacheFile);
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
        if (!file_exists($include)) {
            throw new RuntimeException("File not found: '$include'");
        }
        $routes = [];
        include $include;
        return $routes;
    }
}
