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
use Madsoft\Library\Crud;
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
 * CrudTest
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
class CrudTest extends Test
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
        $crud = $invoker->getInstance(CrudMock::class);
        try {
            $crud->getWherePublic('atable', [], 'NOT VALID LOGIC');
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
        $crud = $this->getCrud();
        $crud->addRow(
            'user',
            [
                'email' => 'testuser@email.com',
                'hash' => 'nohash',
                'token' => 'notoken',
            ]
        );
        $row = $crud->getRow(
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
        $crud->delRows('user', ['email' => 'testuser@email.com']);
        $row = $crud->getRow(
            'user',
            ['email', 'hash', 'token'],
            ['email' => 'testuser@email.com']
        );
        $this->assertEquals(
            [],
            $row
        );
    }
    
    /**
     * Method getCrud
     *
     * @return Crud
     */
    protected function getCrud(): Crud
    {
        $invoker = new Invoker();
        $merger = new Merger();
        $config = new Config($invoker, $merger);
        $transaction = new Transaction();
        $safer = new Safer();
        $mysql = new Mysql($config, $transaction);
        $session = new Session();
        $user = new User($session);
        return new Crud($safer, $mysql, $user);
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
        $crud = $this->getCrud();
        $crud->addRow(
            'user',
            [
                'email' => 'testuser1@email.com',
                'hash' => 'nohash',
                'token' => 'notoken1',
            ]
        );
        $crud->addRow(
            'user',
            [
                'email' => 'testuser2@email.com',
                'hash' => 'nohash',
                'token' => 'notoken2',
            ]
        );
        $crud->addRow(
            'user',
            [
                'email' => 'testuser3@email.com',
                'hash' => 'nohash',
                'token' => 'notoken3',
            ]
        );
        $row = $crud->getRows(
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
        $crud->delRows('user', ['email' => 'testuser1@email.com']);
        $crud->delRows('user', ['email' => 'testuser2@email.com']);
        $crud->delRows('user', ['email' => 'testuser3@email.com']);
        $row = $crud->getRows(
            'user',
            ['email', 'hash', 'token'],
            ['hash' => 'nohash']
        );
        $this->assertEquals(
            [],
            $row
        );
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
        $crud = $this->getCrud();
        $row = $crud->getRow(
            'user',
            ['email', 'hash', 'token'],
            ['email' => 'testuser@email.com']
        );
        $this->assertEquals(
            [],
            $row
        );
        $row = $crud->getRow(
            'user',
            ['email', 'hash', 'token'],
            ['email' => 'testusermodified@email.com']
        );
        $this->assertEquals(
            [],
            $row
        );
        
        
        $crud->addRow(
            'user',
            [
                'email' => 'testuser@email.com',
                'hash' => 'nohash',
                'token' => 'notoken',
            ]
        );
        $result = $crud->setRow(
            'user',
            ['email' => 'testusermodified@email.com'],
            ['email' => 'testuser@email.com'],
        );
        $this->assertEquals(1, $result);
        
        $row = $crud->getRow(
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
        $crud->delRows('user', ['email' => 'testusermodified@email.com']);
        $row = $crud->getRow(
            'user',
            ['email', 'hash', 'token'],
            ['email' => 'testuser@email.com']
        );
        $this->assertEquals(
            [],
            $row
        );
        $row = $crud->getRow(
            'user',
            ['email', 'hash', 'token'],
            ['email' => 'testusermodified@email.com']
        );
        $this->assertEquals(
            [],
            $row
        );
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
        $crud = $this->getCrud();
        $row = $crud->getOwnedRow(
            'user',
            ['email', 'hash', 'token'],
            ['email' => 'testuser@email.com']
        );
        $this->assertEquals(
            [],
            $row
        );
        $row = $crud->getOwnedRow(
            'user',
            ['email', 'hash', 'token'],
            ['email' => 'testusermodified@email.com']
        );
        $this->assertEquals(
            [],
            $row
        );
        
        
        $crud->addOwnedRow(
            'user',
            [
                'email' => 'testuser@email.com',
                'hash' => 'nohash',
                'token' => 'notoken',
            ]
        );
        $result = $crud->setOwnedRow(
            'user',
            ['email' => 'testusermodified@email.com'],
            ['email' => 'testuser@email.com'],
        );
        $this->assertEquals(1, $result);
        
        $row = $crud->getOwnedRow(
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
        $crud->delOwnedRows('user', ['email' => 'testusermodified@email.com']);
        $row = $crud->getOwnedRow(
            'user',
            ['email', 'hash', 'token'],
            ['email' => 'testuser@email.com']
        );
        $this->assertEquals(
            [],
            $row
        );
        $row = $crud->getOwnedRow(
            'user',
            ['email', 'hash', 'token'],
            ['email' => 'testusermodified@email.com']
        );
        $this->assertEquals(
            [],
            $row
        );
        
        $session->unset('uid');
    }
}
