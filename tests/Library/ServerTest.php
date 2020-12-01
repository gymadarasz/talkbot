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

use Madsoft\Library\Server;
use Madsoft\Library\Tester\Test;

/**
 * ServerTest
 *
 * @category  PHP
 * @package   Madsoft\Tests\Library
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */
class ServerTest extends Test
{
    /**
     * Method testServer
     *
     * @param Server $server server
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     *
     * @suppress PhanUnreferencedPublicMethod
     */
    public function testServer(Server $server): void
    {
        $_SERVER['HTTPS'] = 'off';
        $_SERVER['SERVER_NAME'] = 'myserver.test';
        $_SERVER['REQUEST_URI'] = '?foo=bar';
        
        $result = $server->getBaseUrl();
        $this->assertEquals('http://myserver.test', $result);
        $result = $server->getBaseUri();
        $this->assertEquals('http://myserver.test?foo=bar', $result);
        
        $_SERVER['HTTPS'] = 'on';
        $_SERVER['SERVER_NAME'] = 'myserver.test';
        $_SERVER['REQUEST_URI'] = '?foo=bar';
        
        $result = $server->getBaseUrl();
        $this->assertEquals('https://myserver.test', $result);
        $result = $server->getBaseUri();
        $this->assertEquals('https://myserver.test?foo=bar', $result);
    }
}
