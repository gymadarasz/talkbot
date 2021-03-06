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

use Madsoft\Tests\Library\Account\AccountTest;

/**
 * TalkbotApiTest
 *
 * @category  PHP
 * @package   Madsoft\Tests\Library\Crud
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */
class ContentTest extends AccountTest
{
    const USER1_EMAIL = 'user1@testing.com';
    const USER1_PASSWORD = 'User1Password123!';
    const USER2_EMAIL = 'user2@testing.com';
    const USER2_PASSWORD = 'User2Password123!';
    
    /**
     * Variable $routes
     *
     * @var string[]
     */
    protected array $routes = [
        __DIR__ . '/../../../src/Library/routes/api.accounts.routes.php',
        __DIR__ . '/../../../src/Library/routes/api.content.routes.php',
        __DIR__ . '/../../../src/Library/routes/api.my-contents.routes.php',
    ];
    
    /**
     * Method beforeAll
     *
     * @return void
     */
    public function beforeAll(): void
    {
        $this->database->delRows('content');
        
        parent::beforeAll();
        
        $this->canSeeRegistryWorks(self::USER1_EMAIL, self::USER1_PASSWORD);
        $this->canSeeActivationMail(self::USER1_EMAIL);
        $this->canSeeActivationWorks(self::USER1_EMAIL);
        
        $this->canSeeRegistryWorks(self::USER2_EMAIL, self::USER2_PASSWORD);
        $this->canSeeActivationMail(self::USER2_EMAIL);
        $this->canSeeActivationWorks(self::USER2_EMAIL);
        
        $this->canSeeLoginWorks(self::USER1_EMAIL, self::USER1_PASSWORD);
    }
    
    /**
     * Method afterAll
     *
     * @return void
     */
    public function afterAll(): void
    {
        $this->canSeeLogoutWorks();
        $this->database->delRows('content');
        
        parent::afterAll();
    }
    
    /**
     * Method testContent
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     *
     * @suppress PhanUnreferencedPublicMethod
     */
    public function testContent(): void
    {
        // first content list is empty
        $results = $this->getContentListResults('name');
        $this->assertTrue(
            in_array(
                'Empty list',
                $results['messages']['error'],
                true
            )
        );
        
        // then I am going to add some content
        $this->getContentCreateResults('Test content1 of user1', true);
        $this->getContentCreateResults('Test content2 of user1', true);
        $this->getContentCreateResults('Test content3 of user1', true);
        
        $results = $this->getContentListResults('name')['rows'];
        // Then I have to see my contents in the content list
        $this->assertEquals(
            [
                ['name' => 'Test content1 of user1'],
                ['name' => 'Test content2 of user1'],
                ['name' => 'Test content3 of user1'],
            ],
            $results
        );
        
        
        $user1contents = $this->getContentListResults('id,name')['rows'];
        
        // check that I can view my sctipt
        $results = $this->json->decode(
            $this->get(
                'q=content/view&fields=id,name&filter[id]=' . $user1contents[0]['id']
                    . '&csrf=' . $this->getCsrf()
            )
        );
        $this->assertEquals(['id', 'name'], array_keys($results['row']));
        $this->assertEquals('Test content1 of user1', $results['row']['name']);
        
        // check that I can edit my sctipt
        $results = $this->json->decode(
            $this->post(
                'q=my-contents/edit&filter[id]=' . $user1contents[0]['id']
                    . '&csrf=' . $this->getCsrf(),
                [
                    'values' => [
                        'id' => $user1contents[0]['id'],
                        'name' => 'Modified name 1',
                    ]
                ]
            )
        );
        $this->assertEquals(1, $results['affected']);
        $this->assertTrue(
            in_array('Content saved', $results['messages']['success'], true)
        );
        
        // check that I can delete my sctipt
        $results = $this->json->decode(
            $this->get(
                'q=my-contents/delete&filter[id]=' . $user1contents[0]['id']
                    . '&csrf=' . $this->getCsrf()
            )
        );
        $this->assertEquals(1, $results['affected']);
        $this->assertTrue(
            in_array('Operation success', $results['messages']['success'], true)
        );
        
        // I am going to login with other user
        $this->canSeeLogoutWorks();
        $this->canSeeLoginWorks(self::USER2_EMAIL, self::USER2_PASSWORD);
  
        // Then I have to see the ret of published contents in the content list
        $results = $this->getContentListResults(['name', 'published']);
        $this->assertEquals(
            [
                ['name' => 'Test content2 of user1', 'published' => '1'],
                ['name' => 'Test content3 of user1', 'published' => '1'],
            ],
            $results['rows']
        );
        
        // then I am going to add some content
        $this->getContentCreateResults('Test content1 of user2', false);
        $this->getContentCreateResults('Test content2 of user2', false);
        $this->getContentCreateResults('Test content3 of user2', true);
        
        // Then still, I have to see only the published contents in the content list
        $this->assertEquals(
            [
                ['name' => 'Test content2 of user1'],
                ['name' => 'Test content3 of user1'],
                ['name' => 'Test content3 of user2'],
            ],
            $this->getContentListResults('name')['rows']
        );
        
        $user2contents = $this->getContentListResults('id,name')['rows'];
        
        // I am going to login with the first user
        $this->canSeeLogoutWorks();
        $this->canSeeLoginWorks(self::USER1_EMAIL, self::USER1_PASSWORD);
        
        // Then I have to see the published contents in the content list
        $results = $this->getContentListResults(['name', 'published']);
        $this->assertEquals(
            [
                ['name' => 'Test content2 of user1', 'published' => '1'],
                ['name' => 'Test content3 of user1', 'published' => '1'],
                ['name' => 'Test content3 of user2', 'published' => '1'],
            ],
            $results['rows']
        );
        
        // check that I can not view the other users unpublished contents, but
        // list still shows the published contents and only those becase the
        // parameter overrides in route definitions
        $results = $this->json->decode(
            $this->get(
                'q=content/view&fields=name,published'
                    . '&filter[published]=0'
                    . '&filter[id]=' . $user2contents[0]['id']
                    . '&csrf=' . $this->getCsrf()
            )
        );
        $this->assertEquals(
            ['name' => 'Test content2 of user1', 'published' => '1'],
            $results['row']
        );
        $this->assertEquals(
            [
                ['name' => 'Test content2 of user1', 'published' => '1'],
                ['name' => 'Test content3 of user1', 'published' => '1'],
                ['name' => 'Test content3 of user2', 'published' => '1'],
            ],
            $this->getContentListResults('name,published')['rows']
        );
        
        // check that I can not view the other users contents by name
        $results = $this->json->decode(
            $this->get(
                'q=content/view&filter[name]=' . $user2contents[0]['name']
                    . '&csrf=' . $this->getCsrf()
            )
        );
        $this->assertTrue(
            in_array('Invalid parameter(s)', $results['messages']['error'], true)
        );
        $this->assertTrue(
            in_array('Mandatory', $results['errors']['filter.id'], true)
        );
        $this->assertTrue(
            in_array('Not a number', $results['errors']['filter.id'], true)
        );
        
        
        // check that I can not edit the other users contents
        $results = $this->json->decode(
            $this->post(
                'q=my-contents/edit&filter[id]=' . $user1contents[0]['id']
                    . '&csrf=' . $this->getCsrf(),
                [
                    'values' => [
                        'id' => $user1contents[0]['id'],
                        'name' => 'Modified name 1'
                    ]
                ]
            )
        );
        $this->assertTrue(
            in_array('Not found', $results['messages']['error'], true)
        );
        
        // check that I can not edit the other users contents by name
        $results = $this->json->decode(
            $this->post(
                'q=my-contents/edit&filter[name]=' . $user1contents[0]['name']
                    . '&csrf=' . $this->getCsrf(),
                [
                    'values' => [
                        'id' => $user1contents[0]['id'],
                        'name' => 'Modified name 1',
                    ]
                ]
            )
        );
        $this->assertTrue(
            in_array('Not found', $results['messages']['error'], true)
        );
        
    
        // check that I can not delete the other users contents
        $results = $this->json->decode(
            $this->get(
                'q=my-contents/delete&filter[id]=' . $user1contents[0]['id']
                    . '&csrf=' . $this->getCsrf()
            )
        );
        $this->assertTrue(
            in_array('Not affected', $results['messages']['error'], true)
        );
        
        // check that I can not delete other users contents by name
        $results = $this->json->decode(
            $this->get(
                'q=my-contents/delete&filter[name]=' . $user2contents[0]['name']
                    . '&csrf=' . $this->getCsrf()
            )
        );
        
        $this->assertTrue(
            in_array('Invalid parameter(s)', $results['messages']['error'], true)
        );
        $this->assertTrue(
            in_array('Mandatory', $results['errors']['filter.id'], true)
        );
        $this->assertTrue(
            in_array('Not a number', $results['errors']['filter.id'], true)
        );
    }
    
    /**
     * Method getContentListResults
     *
     * @param string[]|string $fields fields
     *
     * @return mixed[]
     */
    protected function getContentListResults($fields): array
    {
        return $this->json->decode(
            $this->get(
                'q=content/list'
                    . '&fields='
                    . (is_string($fields) ? $fields : implode(',', $fields))
                    . '&csrf=' . $this->getCsrf()
            )
        );
    }
    
    /**
     * Method getContentCreateResults
     *
     * @param string $name      name
     * @param bool   $published published
     *
     * @return mixed[]
     */
    protected function getContentCreateResults(string $name, bool $published): array
    {
        $results = $this->json->decode(
            $this->post(
                'q=my-contents/create'
                    . '&csrf=' . $this->getCsrf(),
                [
                    'values' => ['name' => $name, 'published' => $published],
                ]
            )
        );
        $this->assertTrue(
            in_array('Content saved', $results['messages']['success'], true)
        );
        
        return $results;
    }
}
