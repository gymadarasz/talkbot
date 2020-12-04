<?php declare(strict_types = 1);

/**
 * PHP version 7.4
 *
 * @category  PHP
 * @package   Madsoft\Tests\Talkbot
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */

namespace Madsoft\Tests\Talkbot;

use Madsoft\Library\Json;
use Madsoft\Library\Tester\ApiTest;
use Madsoft\Library\Tester\TestCleaner;

/**
 * TalkbotApiTest
 *
 * @category  PHP
 * @package   Madsoft\Tests\Talkbot
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */
class TalkbotApiTest extends ApiTest
{
    /**
     * Method cleanup
     *
     * @return void
     */
    protected function cleanup(): void
    {
        $this->invoker->getInstance(TestCleaner::class)->deleteMails();
    }
    
    /**
     * Method getRoutes
     *
     * @return string[][][][]
     */
    protected function getRoutes(): array
    {
        return $this->router->loadRoutes(
            [
                __DIR__ . '/../../src/Library/Account/routes.php',
                __DIR__ . '/../../src/routes.api.php',
            ]
        );
    }
    
    /**
     * Method testLogin
     *
     * @param Json $json json
     *
     * @return void
     *
     * @suppress PhanUnreferencedPublicMethod
     */
    public function testLogin(Json $json): void
    {
        $response = $this->post(
            'q=login'
                . '&csrf=' . $this->getCsrf(),
            [
                'email' => 'wrongemail@example.com',
                'password' => 'BadPassword'
            ]
        );
        $results = $json->decode($response);
        $this->assertTrue(
            in_array('Login failed', $results['messages']['error'], true)
        );
    }
}
