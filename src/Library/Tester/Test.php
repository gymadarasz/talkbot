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

use Exception;
use Madsoft\Library\App\ApiApp;
use Madsoft\Library\Invoker;
use Mockery;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use RuntimeException;

/**
 * Test
 *
 * @category  PHP
 * @package   Madsoft\Library\Tester
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 *
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
abstract class Test
{
    /**
     * Variable $failInfos
     *
     * @var string[]
     */
    protected array $failInfos = [];
    
    protected int $asserts = 0;
    protected int $success = 0;
    protected int $fails = 0;
    
    /**
     * Variable $globalsStack
     *
     * @var mixed[]
     */
    protected static array $globalsStack = [];
    
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
     * Method get
     *
     * @param callable $include include
     * @param mixed[]  $args    args
     * @param string   $params  params
     *
     * @return string
     * @throws RuntimeException
     *
     * @suppressWarnings(PHPMD.Superglobals)
     */
    protected function get(
        callable $include,
        array $args = [],
        string $params = ''
    ): string {
        $this->pushGlobals();
        $_SERVER['REQUEST_METHOD'] = 'GET';
        parse_str($params, $_GET);
        $_REQUEST = $_GET;
        unset($_POST);
        $contents = $this->getContents($include, $args);
        $this->popGlobals();
        return $contents;
    }
    
    /**
     * Method post
     *
     * @param callable $include include
     * @param mixed[]  $args    args
     * @param string   $params  params
     * @param mixed[]  $data    data
     *
     * @return string
     * @throws RuntimeException
     *
     * @suppressWarnings(PHPMD.Superglobals)
     *
     * @suppress PhanUnreferencedProtectedMethod
     */
    protected function post(
        callable $include,
        array $args = [],
        string $params = '',
        array $data = []
    ): string {
        $this->pushGlobals();
        $_SERVER['REQUEST_METHOD'] = 'POST';
        parse_str($params, $_GET);
        $_REQUEST = $_GET;
        $_POST = $data;
        $_REQUEST = array_merge($_REQUEST, $_POST);
        $contents = $this->getContents($include, $args);
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
     * Method getMock
     *
     * @param string $class class
     *
     * @return MockInterface|LegacyMockInterface
     *
     * @SuppressWarnings(PHPMD)
     *
     * @suppress PhanUndeclaredTypeReturnType
     * @ suppress PhanUndeclaredClassMethod
     * @suppress PhanUnreferencedProtectedMethod
     */
    protected function getMock(string $class)
    {
        return Mockery::mock($class);
    }
    
    /**
     * Method showTick
     *
     * @return void
     */
    protected function showTick(): void
    {
        echo '.';
    }
    
    /**
     * Method showFail
     *
     * @return void
     */
    protected function showFail(): void
    {
        echo 'X';
    }
    
    /**
     * Method varDump
     *
     * @param mixed $var var
     *
     * @return string
     */
    protected function varDump($var): string
    {
        $type = gettype($var);
        return "($type) " . print_r($var, true);
    }
    
    /**
     * Method storeFail
     *
     * @param mixed $expected expected
     * @param mixed $result   result
     * @param mixed $message  message
     *
     * @return void
     */
    protected function storeFail($expected, $result, $message): void
    {
        try {
            throw new Exception();
        } catch (Exception $exception) {
            $trace = $exception->getTraceAsString();
        }
        $this->failInfos[] = "Test failed: $message"
                . "\nExpected: " . $this->varDump($expected)
                . "\nResult: " . $this->varDump($result)
                . "\nTrace:\n"
                . $trace;
    }
    
    /**
     * Method assertTrue
     *
     * @param bool   $result  result
     * @param string $message message
     * @param mixed  $origExp origExp
     * @param mixed  $origRes origRes
     *
     * @return void
     */
    public function assertTrue(
        bool $result,
        string $message = 'Assert true failed.',
        $origExp = null,
        $origRes = null
    ): void {
        $this->asserts++;
        if ($result) {
            $this->success++;
            $this->showTick();
            return;
        }
        $this->fails++;
        $this->showFail();
        $this->storeFail($origExp, $origRes, $message);
    }
    
    /**
     * Method assertFalse
     *
     * @param bool   $result  result
     * @param string $message message
     *
     * @return void
     */
    public function assertFalse(
        bool $result,
        string $message = 'Assert false failed.'
    ): void {
        $this->assertTrue(!$result, $message);
    }
    
    /**
     * Method assertEquals
     *
     * @param mixed  $expected expected
     * @param mixed  $result   result
     * @param string $message  message
     *
     * @return void
     */
    public function assertEquals(
        $expected,
        $result,
        string $message = 'Assert equals failed.'
    ): void {
        $this->assertTrue($expected === $result, $message, $expected, $result);
    }
    
    /**
     * Method assertEquals
     *
     * @param mixed  $expected expected
     * @param mixed  $result   result
     * @param string $message  message
     *
     * @return void
     */
    public function assertNotEquals(
        $expected,
        $result,
        string $message = 'Assert not equals failed.'
    ): void {
        $this->assertTrue($expected !== $result, $message, $expected, $result);
    }
    
    /**
     * Method assertStringContains
     *
     * @param string $expected expected
     * @param string $result   result
     * @param string $message  message
     *
     * @return void
     */
    public function assertStringContains(
        string $expected,
        string $result,
        string $message = 'Assert string contains failed.'
    ): void {
        $this->assertTrue(
            false !== strpos($result, $expected),
            $message,
            $expected,
            $result
        );
    }
    
    /**
     * Method assertStringNotContains
     *
     * @param string $expected expected
     * @param string $result   result
     * @param string $message  message
     *
     * @return void
     *
     * @suppress PhanUnreferencedPublicMethod
     */
    public function assertStringNotContains(
        string $expected,
        string $result,
        string $message = 'Assert string not contains failed.'
    ): void {
        $this->assertTrue(
            false === strpos($result, $expected),
            $message,
            $expected,
            $result
        );
    }
}
