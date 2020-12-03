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

use Madsoft\Library\Invoker;
use Madsoft\Library\Router;
use RuntimeException;

/**
 * CleanTest
 *
 * @category  PHP
 * @package   Madsoft\Library\Tester
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */
abstract class CleanTest extends Test
{
    /**
     * Variable $routes
     *
     * @var string[]
     *
     * @suppress PhanReadOnlyProtectedProperty
     */
    protected array $routes;
    
    /**
     * Variable $globalsStack
     *
     * @var mixed[]
     */
    protected static array $globalsStack = [];
    
    protected Router $router;
    protected Invoker $invoker;

    /**
     * Method __construct
     *
     * @param Router  $router  router
     * @param Invoker $invoker invoker
     */
    public function __construct(Router $router, Invoker $invoker)
    {
        $this->router = $router;
        $this->invoker = $invoker;
    }
    
    /**
     * Method getRoutes
     *
     * @return string[][][][]
     */
    protected function getRoutes(): array
    {
        return $this->router->loadRoutes(
            array_merge(
                $this->routes,
                [__DIR__ . '/../../routes.api.php']
            )
        );
    }
    
    /**
     * Method pushGlobals
     *
     * @return void
     *
     * @suppressWarnings(PHPMD.Superglobals)
     */
    protected function pushGlobals(): void
    {
        array_push(
            self::$globalsStack,
            [
                '_GET' => $_GET ?? null,
                '_POST' => $_POST ?? null,
                '_REQUEST' => $_REQUEST ?? null,
                '_SERVER' => $_SERVER ?? null,
            ]
        );
    }
    
    /**
     * Method popGlobals
     *
     * @return void
     *
     * @suppressWarnings(PHPMD.Superglobals)
     */
    protected function popGlobals(): void
    {
        $storedGlobals = array_pop(self::$globalsStack);
        if (null === $storedGlobals) {
            throw new RuntimeException('Empty globals stack');
        }
        if (null !== $storedGlobals['_GET']) {
            $_GET = $storedGlobals['_GET'];
        }
        if (null !== $storedGlobals['_POST']) {
            $_POST = $storedGlobals['_POST'];
        }
        if (null !== $storedGlobals['_REQUEST']) {
            $_REQUEST = $storedGlobals['_REQUEST'];
        }
        if (null !== $storedGlobals['_SERVER']) {
            $_SERVER = $storedGlobals['_SERVER'];
        }
    }
    
    /**
     * Method beforeAll
     *
     * @return void
     *
     * @suppress PhanUnreferencedPublicMethod
     */
    public function beforeAll(): void
    {
        $this->cleanup();
    }
    
    /**
     * Method before
     *
     * @return void
     *
     * @suppress PhanUnreferencedPublicMethod
     */
    public function before(): void
    {
        $this->pushGlobals();
    }
    
    /**
     * Method after
     *
     * @return void
     *
     * @suppress PhanUnreferencedPublicMethod
     */
    public function after(): void
    {
        $this->popGlobals();
    }
    
    /**
     * Method afterAll
     *
     * @return void
     *
     * @suppress PhanUnreferencedPublicMethod
     */
    public function afterAll(): void
    {
        $this->cleanup();
    }
    
    /**
     * Method cleanup
     *
     * @return void
     */
    abstract protected function cleanup(): void;
}
