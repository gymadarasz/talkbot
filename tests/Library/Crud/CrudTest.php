<?php declare(strict_types = 1);

/**
 * PHP version 7.4
 *
 * @category  PHP
 * @package   Madsoft\Tests\Library\Crud
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */

namespace Madsoft\Tests\Library\Crud;

use Madsoft\Library\Database;
use Madsoft\Library\Json;
use Madsoft\Library\Tester\ApiTest;
use Madsoft\Library\Transaction;
use Madsoft\Library\User;
use function count;

/**
 * CrudTest
 *
 * @category  PHP
 * @package   Madsoft\Tests\Library\Crud
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 *
 * @suppress PhanUnreferencedClass
 */
class CrudTest extends ApiTest
{
    protected Database $database;
    
    /**
     * Variable $routes
     *
     * @var string[]
     */
    protected array $routes = [
        __DIR__ . '/../../../src/Library/Crud/crud.api.routes.php',
    ];
    
    /**
     * Method beforeAll
     *
     * @return void
     */
    public function beforeAll(): void
    {
        parent::beforeAll();
        
        $this->database = $this->invoker->getInstance(Database::class);
        $this->invoker->getInstance(Transaction::class)->start();
        
        $this->invoker->getInstance(User::class)->login(
            $this->createUser('admin1@testing.com', 'admin'),
            'admin'
        );
        
        for ($counter=1; $counter<=10; $counter++) {
            $this->createUser("user$counter@testing.com", 'user');
        }
        
        $this->invoker->getInstance(Transaction::class)->commit();
    }
    
    /**
     * Method createUser
     *
     * @param string $email email
     * @param string $group group
     *
     * @return int|string
     */
    protected function createUser(string $email, string $group)
    {
        return $this->database->addRow(
            'user',
            [
                'email' => $email,
                'group' => $group,
                'hash' => '',
                'token' => null
            ]
        );
    }
    
    /**
     * Method testCrud
     *
     * @param Json $json json
     *
     * @return void
     *
     * @suppress PhanUnreferencedPublicMethod
     */
    public function testListFilter(Json $json): void
    {
        $results = $json->decode(
            $this->get(
                'q=list&'
                    . 'table=user&'
                    . 'fields=email,group&'
                    . 'filter[group]=admin&'
                    . 'csrf=' . $this->getCsrf()
            )
        );
        $this->assertEquals(1, count($results['rows']));
        $this->assertEquals(
            [
                ['email' => 'admin1@testing.com', 'group' => 'admin'],
            ],
            $results['rows']
        );
    }
    
    /**
     * Method testListFilter2
     *
     * @param Json $json json
     *
     * @return void
     *
     * @suppress PhanUnreferencedPublicMethod
     */
    public function testListFilter2(Json $json): void
    {
        $results = $json->decode(
            $this->get(
                'q=list&'
                    . 'table=user&'
                    . 'fields=email,group&'
                    . 'filter[group]=user&'
                    . 'csrf=' . $this->getCsrf()
            )
        );
        $this->assertEquals(10, count($results['rows']));
        $this->assertTrue(in_array('rows', array_keys($results), true));
        $counter = 1;
        foreach ($results['rows'] as $row) {
            $this->assertEquals(
                [
                    'email' => "user$counter@testing.com",
                    'group' => 'user',
                ],
                $row
            );
            $counter++;
        }
    }
    
    /**
     * Method testListFilter3
     *
     * @param Json $json json
     *
     * @return void
     *
     * @suppress PhanUnreferencedPublicMethod
     */
    public function testListFilter3(Json $json): void
    {
        $results = $json->decode(
            $this->get(
                'q=list&'
                    . 'table=user&'
                    . 'fields=email,group&'
                    . 'filter[email]=admin1@testing.com&'
                    . 'filter[group]=user&'
                    . 'csrf=' . $this->getCsrf()
            )
        );
        
        $this->assertEquals(
            $this->getEmptyListErrorResponse()['messages'],
            $results['messages']
        );
    }
    
    /**
     * Method testListFilterLogic
     *
     * @param Json $json json
     *
     * @return void
     *
     * @suppress PhanUnreferencedPublicMethod
     */
    public function testListFilterLogic(Json $json): void
    {
        $results = $json->decode(
            $this->get(
                'q=list&'
                    . 'table=user&'
                    . 'fields=email,group&'
                    . 'filter[email]=admin1@testing.com&'
                    . 'filter[group]=user&'
                    . 'filterLogic=OR&'
                    . 'csrf=' . $this->getCsrf()
            )
        );
        
        $this->assertEquals(11, count($results['rows']));
        $this->assertTrue(in_array('rows', array_keys($results), true));
        $counter = 0;
        foreach ($results['rows'] as $row) {
            if ($counter === 0) {
                $this->assertEquals(
                    [
                        'email' => "admin1@testing.com",
                        'group' => 'admin',
                    ],
                    $row
                );
                $counter++;
                continue;
            }
            $this->assertEquals(
                [
                    'email' => "user$counter@testing.com",
                    'group' => 'user',
                ],
                $row
            );
            $counter++;
        }
    }
    
    /**
     * Method testListFilterLimitOffset
     *
     * @param Json $json json
     *
     * @return void
     *
     * @suppress PhanUnreferencedPublicMethod
     */
    public function testListFilterLimitOffset(Json $json): void
    {
        $results = $json->decode(
            $this->get(
                'q=list&'
                    . 'table=user&'
                    . 'fields=email,group&'
                    . 'filter[group]=user&'
                    . 'limit=3&offset=5&'
                    . 'csrf=' . $this->getCsrf()
            )
        );
        $this->assertEquals(3, count($results['rows']));
        $this->assertTrue(in_array('rows', array_keys($results), true));
        $counter = 6;
        foreach ($results['rows'] as $row) {
            $this->assertEquals(
                [
                    'email' => "user$counter@testing.com",
                    'group' => 'user',
                ],
                $row
            );
            $counter++;
        }
    }
    
    /**
     * Method testView
     *
     * @param Json $json json
     *
     * @return void
     *
     * @suppress PhanUnreferencedPublicMethod
     */
    public function testView(Json $json): void
    {
        $results = $json->decode(
            $this->get(
                'q=view&'
                    . 'table=user&'
                    . 'fields=email,group&'
                    . 'filter[email]=user3@testing.com&'
                    . 'csrf=' . $this->getCsrf()
            )
        );
        
        $this->assertEquals('user3@testing.com', $results['row']['email']);
        $this->assertEquals('user', $results['row']['group']);
    }
    
    /**
     * Method testViewNotFound
     *
     * @param Json $json json
     *
     * @return void
     *
     * @suppress PhanUnreferencedPublicMethod
     */
    public function testViewNotFound(Json $json): void
    {
        $results = $json->decode(
            $this->get(
                'q=view&'
                    . 'table=user&'
                    . 'fields=email,group&'
                    . 'filter[email]=not-exist-email&'
                    . 'csrf=' . $this->getCsrf()
            )
        );
        
        $this->assertEquals(
            $this->getNotFoundErrorResponse()['messages'],
            $results['messages']
        );
    }
    
    /**
     * Method testEdit
     *
     * @param Json $json json
     *
     * @return void
     *
     * @suppress PhanUnreferencedPublicMethod
     */
    public function testEdit(Json $json): void
    {
        $tmpUser = $json->decode(
            $this->get(
                'q=list&table=user&fields=id,email'
                    . '&csrf=' . $this->getCsrf()
            )
        )['rows'][0];
        
        $this->checkSetUserEmail($json, (int)$tmpUser['id'], 'temp@email.com');
        $this->checkSetUserEmail($json, (int)$tmpUser['id'], $tmpUser['email']);
    }
    
    /**
     * Method testCreateDelete
     *
     * @param Json $json json
     *
     * @return void
     *
     * @suppress PhanUnreferencedPublicMethod
     */
    public function testCreateDelete(Json $json): void
    {
        $this->checkEmailNotExists($json, 'createduser1@testing.com');
        
        $result = $json->decode(
            $this->post(
                'q=create&table=user'
                    . '&csrf=' . $this->getCsrf(),
                [
                    'values' =>
                    [
                        'email' => 'createduser1@testing.com',
                        'hash' => ''
                    ]
                ]
            )
        );
        $this->assertTrue(
            in_array('Operation success', $result['messages']['success'], true)
        );
        $uid = (int)$result['insertId'];
        
        $user = $json->decode(
            $this->get(
                'q=view&table=user&fields=id,email&filter[id]=' . $uid
                    . '&csrf=' . $this->getCsrf()
            )
        )['row'];
        $this->assertEquals($uid, (int)$user['id']);
        $this->assertEquals('createduser1@testing.com', $user['email']);
        
        $result = $json->decode(
            $this->get(
                'q=delete&table=user&filter[id]=' . $uid
                    . '&csrf=' . $this->getCsrf()
            )
        );
        $this->assertTrue(
            in_array('Operation success', $result['messages']['success'], true)
        );
        $this->assertEquals(1, (int)$result['affected']);
        
        $this->checkEmailNotExists($json, 'createduser1@testing.com');
    }
    
    /**
     * Method testValidatons
     *
     * @param Json $json json
     *
     * @return void
     *
     * @suppress PhanUnreferencedPublicMethod
     */
    public function testValidatons(Json $json): void
    {
        $results = $json->decode($this->get('q=list&csrf=' . $this->getCsrf()));
        $this->assertTrue(
            in_array('Invalid parameter(s)', $results['messages']['error'], true)
        );
        $this->assertEquals(['table' =>['Mandatory']], $results['errors']);
        
        $results = $json->decode($this->post('q=create&csrf=' . $this->getCsrf()));
        $this->assertTrue(
            in_array('Invalid parameter(s)', $results['messages']['error'], true)
        );
        $this->assertEquals(['table' =>['Mandatory']], $results['errors']);
        
        $results = $json->decode($this->post('q=edit&csrf=' . $this->getCsrf()));
        $this->assertTrue(
            in_array('Invalid parameter(s)', $results['messages']['error'], true)
        );
        $this->assertEquals(['table' =>['Mandatory']], $results['errors']);
        
        $results = $json->decode($this->get('q=delete&csrf=' . $this->getCsrf()));
        $this->assertTrue(
            in_array('Invalid parameter(s)', $results['messages']['error'], true)
        );
        $this->assertEquals(['table' =>['Mandatory']], $results['errors']);
    }

    /**
     * Method checkEmailNotExists
     *
     * @param Json   $json  json
     * @param string $email email
     *
     * @return void
     */
    protected function checkEmailNotExists(Json $json, string $email): void
    {
        $tmpUsers = $json->decode(
            $this->get(
                'q=list&table=user&fields=id,email'
                    . '&csrf=' . $this->getCsrf()
            )
        )['rows'];
        
        foreach ($tmpUsers as $user) {
            $this->assertNotEquals($email, $user['email']);
        }
    }
    
    /**
     * Method checkSetUserEmail
     *
     * @param Json   $json  json
     * @param int    $uid   uid
     * @param string $email email
     *
     * @return void
     */
    protected function checkSetUserEmail(Json $json, int $uid, string $email): void
    {
        $result = $json->decode(
            $this->post(
                'q=edit&table=user&filter[id]=' . $uid
                    . '&csrf=' . $this->getCsrf(),
                [
                    'values' =>
                    [
                        'email' => $email,
                    ]
                ]
            )
        );
        $this->assertTrue(
            in_array('Operation success', $result['messages']['success'], true)
        );
        $this->assertEquals(1, $result['affected']);
        
        $user = $json->decode(
            $this->get(
                'q=view&table=user&fields=id,email&filter[id]=' . $uid
                    . '&csrf=' . $this->getCsrf()
            )
        )['row'];
        $this->assertEquals($uid, (int)$user['id']);
        $this->assertEquals($email, $user['email']);
    }
    
    /**
     * Method getEmptyListErrorResponse
     *
     * @return string[][][]
     */
    protected function getEmptyListErrorResponse(): array
    {
        return [
            'messages' =>
            [
                'error' =>
                [
                    'Empty list'
                ]
            ],
        ];
    }
    
    /**
     * Method getNotFoundErrorResponse
     *
     * @return string[][][]
     */
    protected function getNotFoundErrorResponse(): array
    {
        return [
            'messages' =>
            [
                'error' =>
                [
                    'Not found'
                ]
            ],
        ];
    }
}
