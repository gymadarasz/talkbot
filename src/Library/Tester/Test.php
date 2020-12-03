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
use Mockery;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;

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
