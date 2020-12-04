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
use Madsoft\Library\Router;
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
    protected Json $json;

    /**
     * Method __construct
     *
     * @param Router  $router  router
     * @param Invoker $invoker invoker
     * @param Json    $json    json
     */
    public function __construct(
        Router $router,
        Invoker $invoker,
        Json $json
    ) {
        parent::__construct($router, $invoker);
        $this->json = $json;
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
        $contents = $this->getContents([$this, 'callApi'], [$this->getRoutes()]);
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
        $contents = $this->getContents([$this, 'callApi'], [$this->getRoutes()]);
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
        ob_start();
        $include(...$args);
        $contents = ob_get_contents();
        ob_end_clean();
        if (false === $contents) {
            throw new RuntimeException("Output buffering isn't active");
        }
        return $contents;
    }
    
    /**
     * Method callApi
     *
     * @param string[][][][] $routes routes
     *
     * @return void
     */
    public function callApi(array $routes): void
    {
        echo (new ApiApp(new Invoker()))->setRoutes($routes)->getOutputJson();
    }
}
