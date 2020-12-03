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
        __DIR__ . '/../../../src/Library/Crud/routes.php',
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
     * @return int
     */
    protected function createUser(string $email, string $group): int
    {
        return $this->database->addRow(
            'user',
            [
                'email' => $email,
                'group' => $group,
                'hash' => '',
                'token' => ''
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
                    . 'filter[group]=admin'
            )
        );
        $this->assertEquals(1, count($results['rows']));
        $this->assertEquals(
            [
                'rows' =>
                [
                    ['email' => 'admin1@testing.com', 'group' => 'admin'],
                ]
            ],
            $results
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
                    . 'filter[group]=user'
            )
        );
        $this->assertEquals(10, count($results['rows']));
        $this->assertEquals(['rows'], array_keys($results));
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
                    . 'filter[group]=user'
            )
        );
        
        $this->assertEquals(
            $this->getEmptyListErrorResponse(),
            $results
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
                    . 'filterLogic=OR'
            )
        );
        
        $this->assertEquals(11, count($results['rows']));
        $this->assertEquals(['rows'], array_keys($results));
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
                    . 'limit=3&offset=5'
            )
        );
        $this->assertEquals(3, count($results['rows']));
        $this->assertEquals(['rows'], array_keys($results));
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
                    . 'filter[email]=user3@testing.com'
            )
        );
        
        $this->assertEquals(
            [
                'email' => 'user3@testing.com',
                'group' => 'user',
            ],
            $results
        );
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
                    . 'filter[email]=not-exist-email'
            )
        );
        
        $this->assertEquals(
            $this->getNotFoundErrorResponse(),
            $results
        );
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
