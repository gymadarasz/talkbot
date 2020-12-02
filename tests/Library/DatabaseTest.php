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

use Madsoft\Library\Config;
use Madsoft\Library\Database;
use Madsoft\Library\Invoker;
use Madsoft\Library\Merger;
use Madsoft\Library\Mysql;
use Madsoft\Library\Safer;
use Madsoft\Library\Session;
use Madsoft\Library\Tester\Test;
use Madsoft\Library\Transaction;
use Madsoft\Library\User;
use RuntimeException;

/**
 * DatabaseTest
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
class DatabaseTest extends Test
{
    /**
     * Method testCrudInvalidLogicFails
     *
     * @param Invoker $invoker invoker
     *
     * @return void
     *
     * @suppress PhanUnreferencedPublicMethod
     */
    public function testCrudInvalidLogicFails(Invoker $invoker): void
    {
        $exc = null;
        $database = $invoker->getInstance(DatabaseMock::class);
        try {
            $database->getWherePublic('atable', [], 'NOT VALID LOGIC');
            $this->assertTrue(false);
        } catch (RuntimeException $exc) {
            $this->assertTrue(true);
        }
        $this->assertNotEquals(null, $exc);
    }
    
    /**
     * Method testGetRow
     *
     * @return void
     *
     * @suppress PhanUnreferencedPublicMethod
     */
    public function testGetRow(): void
    {
        $database = $this->getDatabase();
        $database->addRow(
            'user',
            [
                'email' => 'testuser@email.com',
                'hash' => 'nohash',
                'token' => 'notoken',
            ]
        );
        $row = $database->getRow(
            'user',
            ['email', 'hash', 'token'],
            ['email' => 'testuser@email.com']
        );
        $this->assertEquals(
            [
                'email' => 'testuser@email.com',
                'hash' => 'nohash',
                'token' => 'notoken',
            ],
            $row
        );
        
        // cleanup
        $database->delRows('user', ['email' => 'testuser@email.com']);
        try {
            $code = 0;
            $database->getRow(
                'user',
                ['email', 'hash', 'token'],
                ['email' => 'testuser@email.com']
            );
            $this->assertTrue(false);
        } catch (RuntimeException $exception) {
            $code = $exception->getCode();
        }
        $this->assertNotEquals(0, $code);
        $this->assertEquals(Mysql::MYSQL_ERROR, $code);
    }
    
    /**
     * Method getCrud
     *
     * @return Database
     */
    protected function getDatabase(): Database
    {
        $invoker = new Invoker();
        $merger = new Merger();
        $config = new Config($invoker, $merger);
        $transaction = new Transaction();
        $safer = new Safer();
        $mysql = new Mysql($config, $transaction);
        $session = new Session();
        $user = new User($session);
        return new Database($safer, $mysql, $user);
    }
    
    /**
     * Method testGetRows
     *
     * @return void
     *
     * @suppress PhanUnreferencedPublicMethod
     */
    public function testGetRows(): void
    {
        $database = $this->getDatabase();
        $database->addRow(
            'user',
            [
                'email' => 'testuser1@email.com',
                'hash' => 'nohash',
                'token' => 'notoken1',
            ]
        );
        $database->addRow(
            'user',
            [
                'email' => 'testuser2@email.com',
                'hash' => 'nohash',
                'token' => 'notoken2',
            ]
        );
        $database->addRow(
            'user',
            [
                'email' => 'testuser3@email.com',
                'hash' => 'nohash',
                'token' => 'notoken3',
            ]
        );
        $row = $database->getRows(
            'user',
            ['email', 'hash', 'token'],
            ['hash' => 'nohash']
        );
        $this->assertEquals(
            [
                [
                    'email' => 'testuser1@email.com',
                    'hash' => 'nohash',
                    'token' => 'notoken1',
                ],
                [
                    'email' => 'testuser2@email.com',
                    'hash' => 'nohash',
                    'token' => 'notoken2',
                ],
                [
                    'email' => 'testuser3@email.com',
                    'hash' => 'nohash',
                    'token' => 'notoken3',
                ],
            ],
            $row
        );
        
        // clean up
        $database->delRows('user', ['email' => 'testuser1@email.com']);
        $database->delRows('user', ['email' => 'testuser2@email.com']);
        $database->delRows('user', ['email' => 'testuser3@email.com']);
        try {
            $code = 0;
            $database->getRows(
                'user',
                ['email', 'hash', 'token'],
                ['hash' => 'nohash']
            );
            $this->assertTrue(false);
        } catch (RuntimeException $exception) {
            $code = $exception->getCode();
        }
        $this->assertNotEquals(0, $code);
        $this->assertEquals(Mysql::MYSQL_ERROR, $code);
    }
    
    /**
     * Method testSetRow
     *
     * @return void
     *
     * @suppress PhanUnreferencedPublicMethod
     */
    public function testSetRow(): void
    {
        $database = $this->getDatabase();
        try {
            $code = 0;
            $database->getRow(
                'user',
                ['email', 'hash', 'token'],
                ['email' => 'testuser@email.com']
            );
            $this->assertTrue(false);
        } catch (RuntimeException $exception) {
            $code = $exception->getCode();
        }
        $this->assertNotEquals(0, $code);
        $this->assertEquals(Mysql::MYSQL_ERROR, $code);
        
        try {
            $code = 0;
            $database->getRow(
                'user',
                ['email', 'hash', 'token'],
                ['email' => 'testusermodified@email.com']
            );
            $this->assertTrue(false);
        } catch (RuntimeException $exception) {
            $code = $exception->getCode();
        }
        $this->assertNotEquals(0, $code);
        $this->assertEquals(Mysql::MYSQL_ERROR, $code);
        
        $database->addRow(
            'user',
            [
                'email' => 'testuser@email.com',
                'hash' => 'nohash',
                'token' => 'notoken',
            ]
        );
        $result = $database->setRow(
            'user',
            ['email' => 'testusermodified@email.com'],
            ['email' => 'testuser@email.com'],
        );
        $this->assertEquals(1, $result);
        
        $row = $database->getRow(
            'user',
            ['email', 'hash', 'token'],
            ['email' => 'testusermodified@email.com']
        );
        $this->assertEquals(
            [
                'email' => 'testusermodified@email.com',
                'hash' => 'nohash',
                'token' => 'notoken',
            ],
            $row
        );
        
        // cleanup
        $database->delRows('user', ['email' => 'testusermodified@email.com']);
        try {
            $code = 0;
            $database->getRow(
                'user',
                ['email', 'hash', 'token'],
                ['email' => 'testuser@email.com']
            );
            $this->assertTrue(false);
        } catch (RuntimeException $exception) {
            $code = $exception->getCode();
        }
        $this->assertNotEquals(0, $code);
        $this->assertEquals(Mysql::MYSQL_ERROR, $code);
        
        try {
            $code = 0;
            $database->getRow(
                'user',
                ['email', 'hash', 'token'],
                ['email' => 'testusermodified@email.com']
            );
            $this->assertTrue(false);
        } catch (RuntimeException $exception) {
            $code = $exception->getCode();
        }
        $this->assertNotEquals(0, $code);
        $this->assertEquals(Mysql::MYSQL_ERROR, $code);
    }
    
    /**
     * Method testSetOwnedRow
     *
     * @param Session $session session
     *
     * @return void
     *
     * @suppress PhanUnreferencedPublicMethod
     */
    public function testSetOwnedRow(Session $session): void
    {
        $session->set('uid', 1);
        $database = $this->getDatabase();
        try {
            $code = 0;
            $database->getOwnedRow(
                'user',
                ['email', 'hash', 'token'],
                ['email' => 'testuser@email.com']
            );
            $this->assertTrue(false);
        } catch (RuntimeException $exception) {
            $code = $exception->getCode();
        }
        $this->assertNotEquals(0, $code);
        $this->assertEquals(Mysql::MYSQL_ERROR, $code);
        
        try {
            $code = 0;
            $database->getOwnedRow(
                'user',
                ['email', 'hash', 'token'],
                ['email' => 'testusermodified@email.com']
            );
            $this->assertTrue(false);
        } catch (RuntimeException $exception) {
            $code = $exception->getCode();
        }
        $this->assertNotEquals(0, $code);
        $this->assertEquals(Mysql::MYSQL_ERROR, $code);
        
        $database->addOwnedRow(
            'user',
            [
                'email' => 'testuser@email.com',
                'hash' => 'nohash',
                'token' => 'notoken',
            ]
        );
        $result = $database->setOwnedRow(
            'user',
            ['email' => 'testusermodified@email.com'],
            ['email' => 'testuser@email.com'],
        );
        $this->assertEquals(1, $result);
        
        $row = $database->getOwnedRow(
            'user',
            ['email', 'hash', 'token'],
            ['email' => 'testusermodified@email.com']
        );
        $this->assertEquals(
            [
                'email' => 'testusermodified@email.com',
                'hash' => 'nohash',
                'token' => 'notoken',
            ],
            $row
        );
        
        // cleanup
        $database->delOwnedRows('user', ['email' => 'testusermodified@email.com']);
        try {
            $code = 0;
            $database->getOwnedRow(
                'user',
                ['email', 'hash', 'token'],
                ['email' => 'testuser@email.com']
            );
            $this->assertTrue(false);
        } catch (RuntimeException $exception) {
            $code = $exception->getCode();
        }
        $this->assertNotEquals(0, $code);
        $this->assertEquals(Mysql::MYSQL_ERROR, $code);
        
        try {
            $code = 0;
            $database->getOwnedRow(
                'user',
                ['email', 'hash', 'token'],
                ['email' => 'testusermodified@email.com']
            );
            $this->assertTrue(false);
        } catch (RuntimeException $exception) {
            $code = $exception->getCode();
        }
        $this->assertNotEquals(0, $code);
        $this->assertEquals(Mysql::MYSQL_ERROR, $code);
        
        $session->unset('uid');
    }
}
