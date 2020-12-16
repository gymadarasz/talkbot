<?php declare(strict_types = 1);

/**
 * PHP version 7.4
 *
 * @category  PHP
 * @package   Madsoft\Library\Tester
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */

namespace Madsoft\Library\Tester;

use Madsoft\Library\App\ApiApp;
use Madsoft\Library\Invoker;
use Madsoft\Library\Json;
use Madsoft\Library\RouteCache;
use RuntimeException;

/**
 * ApiTest
 *
 * @category  PHP
 * @package   Madsoft\Library\Tester
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */
abstract class ApiTest extends CleanTest
{
    /**
     * Variable $routes
     *
     * @var string[]
     *
     * @suppress PhanReadOnlyProtectedProperty
     */
    protected array $routes = [];
    
    protected Json $json;
    protected RouteCache $routeCache;

    /**
     * Method __construct
     *
     * @param Invoker    $invoker    invoker
     * @param Json       $json       json
     * @param RouteCache $routeCache routeCache
     */
    public function __construct(
        Invoker $invoker,
        Json $json,
        RouteCache $routeCache
    ) {
        parent::__construct($invoker);
        $this->json = $json;
        $this->routeCache = $routeCache;
    }
    
    /**
     * Method getRoutes
     *
     * @param string $routeCacheFilePrefix routeCacheFilePrefix
     *
     * @return mixed[]
     */
    protected function getRoutes(
        string $routeCacheFilePrefix = 'api'
    ): array {
        return $this->routeCache->loadRoutes($this->routes, $routeCacheFilePrefix);
    }
    
    /**
     * Method getCsrf
     *
     * @return int
     */
    public function getCsrf(): int
    {
        return $this->json->decode($this->get('q=csrf'))['csrf'];
    }
    
    /**
     * Method get
     *
     * @param string $params params
     *
     * @return string
     * @throws RuntimeException
     *
     * @suppressWarnings(PHPMD.Superglobals)
     */
    protected function get(
        string $params = ''
    ): string {
        $this->pushGlobals();
        $_SERVER['REQUEST_METHOD'] = 'GET';
        parse_str($params, $_GET);
        $_REQUEST = $_GET;
        unset($_POST);
        $contents = $this->getContents(
            [$this, 'callApi'],
            [$this->getRoutes('api')]
        );
        $this->popGlobals();
        return $contents;
    }
    
    /**
     * Method post
     *
     * @param string  $params params
     * @param mixed[] $data   data
     *
     * @return string
     * @throws RuntimeException
     *
     * @suppressWarnings(PHPMD.Superglobals)
     *
     * @suppress PhanUnreferencedProtectedMethod
     */
    protected function post(
        string $params = '',
        array $data = []
    ): string {
        $this->pushGlobals();
        $_SERVER['REQUEST_METHOD'] = 'POST';
        parse_str($params, $_GET);
        $_REQUEST = $_GET;
        $_POST = $data;
        $_REQUEST = array_merge($_REQUEST, $_POST);
        $contents = $this->getContents(
            [$this, 'callApi'],
            [$this->getRoutes('api')]
        );
        $this->popGlobals();
        return $contents;
    }
    
    /**
     * Method getContents
     *
     * @param callable $include include
     * @param mixed[]  $args    args
     *
     * @return string
     * @throws RuntimeException
     */
    protected function getContents(callable $include, array $args = []): string
    {
        $contents = $include(...$args);
        return $contents;
    }
    
    /**
     * Method callApi
     *
     * @param mixed[] $routes routes
     *
     * @return string
     */
    public function callApi(array $routes): string
    {
        return (new ApiApp(new Invoker(), $this->routeCache))
            ->setRoutes($routes)
            ->getOutput();
    }
}
