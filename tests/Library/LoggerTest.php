<?php declare(strict_types = 1);

/**
 * PHP version 7.4
 *
 * @category  PHP
 * @package   Madsoft\Tests\Library
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */

namespace Madsoft\Tests\Library;

use Exception;
use Madsoft\Library\Invoker;
use Madsoft\Library\Logger;
use Madsoft\Library\Tester\Test;
use Madsoft\Tests\Library\LoggerMock;
use RuntimeException;
use function count;

/**
 * LoggerTest
 *
 * @category  PHP
 * @package   Madsoft\Tests\Library
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 *
 * @suppress PhanUnreferencedClass
 */
class LoggerTest extends Test
{
    /**
     * Method testChannels
     *
     * @param Invoker $invoker invoker
     *
     * @return void
     *
     * @suppress PhanUnreferencedPublicMethod
     */
    public function testChannels(Invoker $invoker): void
    {
        $logger = $invoker->getInstance(Logger::class);
        $channels = $logger->getChannels();
        $this->assertEquals([], $channels);
        $logger->setChannels([1,2,3]);
        $channels = $logger->getChannels();
        $this->assertEquals([1,2,3], $channels);
        $logger->setChannels([]);
        $channels = $logger->getChannels();
        $this->assertEquals([], $channels);
    }
    
    /**
     * Method testCollection
     *
     * @param Invoker $invoker invoker
     *
     * @return void
     *
     * @suppress PhanUnreferencedPublicMethod
     */
    public function testCollection(Invoker $invoker): void
    {
        $logger = $invoker->getInstance(Logger::class);
        
        $collect = $logger->isCollect();
        $this->assertFalse($collect);
        $collection = $logger->getCollection();
        $this->assertTrue(empty($collection));
        
        $logger->setCollect(true);
        $logger->warning('wrn1');
        $logger->info('nfo2');
        $logger->debug('dbg3');
        $logger->test('tst4');
        $logger->fail('fil5');
        $logger->exception(new Exception('exc6', 6, new Exception('prv7', 7)));
        $collection = $logger->getCollection();
        $this->assertEquals(6, count($collection));
        $this->assertStringContains('wrn1', $collection[0]);
        $this->assertStringContains('nfo2', $collection[1]);
        $this->assertStringContains('dbg3', $collection[2]);
        $this->assertStringContains('tst4', $collection[3]);
        $this->assertStringContains('fil5', $collection[4]);
        $this->assertStringContains('exc6', $collection[5]);
        $this->assertStringContains('prv7', $collection[5]);
        $logger->setCollect(false);
        $logger->clearCollection();
        
        $collect = $logger->isCollect();
        $this->assertFalse($collect);
        $collection = $logger->getCollection();
        $this->assertTrue(empty($collection));
    }
    
    /**
     * Method testLogFileErrorFails
     *
     * @return void
     *
     * @suppress PhanUnreferencedPublicMethod
     */
    public function testLogFileErrorFails(): void
    {
        $errlvl = error_reporting();
        error_reporting(0);
        $loggerMock = new LoggerMock();
        try {
            $loggerMock->debug('foo');
            $this->assertTrue(false);
        } catch (RuntimeException $exception) {
            $this->assertStringContains('foo', $exception->getMessage());
        }
        error_reporting($errlvl);
    }
}
