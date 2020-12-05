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

use Madsoft\Library\Router;
use Madsoft\Tests\Library\Account\AccountTest;

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
class TalkbotApiTest extends AccountTest
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
        __DIR__ . '/../../src/Library/Account/routes.php',
        __DIR__ . '/../../src/Talkbot/routes.php',
    ];
    
    /**
     * Method beforeAll
     *
     * @return void
     */
    public function beforeAll(): void
    {
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
        parent::afterAll();
    }
    
    /**
     * Method testScript
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     *
     * @suppress PhanUnreferencedPublicMethod
     */
    public function testScript(): void
    {
        // first script list is empty
        $results = $this->getScriptListResults('name');
        $this->assertTrue(
            in_array(
                'Empty list',
                $results['messages']['error'],
                true
            )
        );
        
        // then I am going to add some script
        $this->getScriptCreateResults('Test script1 of user1');
        $this->getScriptCreateResults('Test script2 of user1');
        $this->getScriptCreateResults('Test script3 of user1');
        
        // Then I have to see my scripts in the script list
        $this->assertEquals(
            [
                ['name' => 'Test script1 of user1'],
                ['name' => 'Test script2 of user1'],
                ['name' => 'Test script3 of user1'],
            ],
            $this->getScriptListResults('name')['rows']
        );
        
        
        $user1scripts = $this->getScriptListResults('id,name')['rows'];
        
        // check that I can view my sctipt
        $results = $this->json->decode(
            $this->get(
                'q=script/view&fields=id,name&filter[id]=' . $user1scripts[0]['id']
                    . '&csrf=' . $this->getCsrf()
            )
        );
        $this->assertEquals(['id', 'name'], array_keys($results['row']));
        $this->assertEquals('Test script1 of user1', $results['row']['name']);
        
        // check that I can edit my sctipt
        $results = $this->json->decode(
            $this->post(
                'q=script/edit&filter[id]=' . $user1scripts[0]['id']
                    . '&csrf=' . $this->getCsrf(),
                [
                    'values' => ['name' => 'Modified name 1']
                ]
            )
        );
        $this->assertEquals(1, $results['affected']);
        $this->assertTrue(
            in_array('Operation success', $results['messages']['success'], true)
        );
        
        // check that I can delete my sctipt
        $results = $this->json->decode(
            $this->get(
                'q=script/delete&filter[id]=' . $user1scripts[0]['id']
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
        
        // I see script list is empty because non of those scripts are mine
        $results = $this->getScriptListResults('name');
        $this->assertTrue(
            in_array(
                'Empty list',
                $results['messages']['error'],
                true
            )
        );
        
        // then I am going to add some script
        $this->getScriptCreateResults('Test script1 of user2');
        $this->getScriptCreateResults('Test script2 of user2');
        $this->getScriptCreateResults('Test script3 of user2');
        
        // Then I have to see my scripts in the script list
        $this->assertEquals(
            [
                ['name' => 'Test script1 of user2'],
                ['name' => 'Test script2 of user2'],
                ['name' => 'Test script3 of user2'],
            ],
            $this->getScriptListResults('name')['rows']
        );
        
        $user2scripts = $this->getScriptListResults('id,name')['rows'];
        
        // I am going to login with the first user
        $this->canSeeLogoutWorks();
        $this->canSeeLoginWorks(self::USER1_EMAIL, self::USER1_PASSWORD);
        
        // Then I have to see my scripts in the script list
        $results = $this->getScriptListResults('name');
        $this->assertEquals(
            [
                // deleted: ['name' => 'Test script1 of user1'],
                ['name' => 'Test script2 of user1'],
                ['name' => 'Test script3 of user1'],
            ],
            $results['rows']
        );
        
        // check that I can not view the other users scripts
        $results = $this->json->decode(
            $this->get(
                'q=script/view&filter[id]=' . $user2scripts[0]['id']
                    . '&csrf=' . $this->getCsrf()
            )
        );
        $this->assertTrue(
            in_array('Not found', $results['messages']['error'], true)
        );
        
        // check that I can not view the other users scripts by name
        $results = $this->json->decode(
            $this->get(
                'q=script/view&filter[name]=' . $user2scripts[0]['name']
                    . '&csrf=' . $this->getCsrf()
            )
        );
        $this->assertTrue(
            in_array(Router::ERR_EXCEPTION, $results['messages']['error'], true)
        );
        
        // check that I can not edit the other users scripts
        $results = $this->json->decode(
            $this->post(
                'q=script/edit&filter[id]=' . $user2scripts[0]['id']
                    . '&csrf=' . $this->getCsrf(),
                [
                    'values' => ['name' => 'Modified name 1']
                ]
            )
        );
        $this->assertTrue(
            in_array(Router::ERR_EXCEPTION, $results['messages']['error'], true)
        );
        
        // check that I can not edit the other users scripts by name
        $results = $this->json->decode(
            $this->post(
                'q=script/edit&filter[name]=' . $user2scripts[0]['name']
                    . '&csrf=' . $this->getCsrf(),
                [
                    'values' => ['name' => 'Modified name 1']
                ]
            )
        );
        $this->assertTrue(
            in_array(Router::ERR_EXCEPTION, $results['messages']['error'], true)
        );
        
        // check that I can not delete the other users scripts
        $results = $this->json->decode(
            $this->get(
                'q=script/delete&filter[id]=' . $user2scripts[0]['id']
                    . '&csrf=' . $this->getCsrf()
            )
        );
        $this->assertTrue(
            in_array(Router::ERR_EXCEPTION, $results['messages']['error'], true)
        );
        
        // check that I can not delete other users strings by name
        $results = $this->json->decode(
            $this->get(
                'q=script/delete&filter[name]=' . $user2scripts[0]['name']
                    . '&csrf=' . $this->getCsrf()
            )
        );
        $this->assertTrue(
            in_array(Router::ERR_EXCEPTION, $results['messages']['error'], true)
        );
    }
    
    /**
     * Method getScriptListResults
     *
     * @param string[]|string $fields fields
     *
     * @return mixed[]
     */
    protected function getScriptListResults($fields): array
    {
        return $this->json->decode(
            $this->get(
                'q=script/list'
                    . '&fields='
                    . (is_string($fields) ? $fields : implode(',', $fields))
                    . '&csrf=' . $this->getCsrf()
            )
        );
    }
    
    /**
     * Method getScriptCreateResults
     *
     * @param string $name name
     *
     * @return mixed[]
     */
    protected function getScriptCreateResults(string $name): array
    {
        $results = $this->json->decode(
            $this->post(
                'q=script/create'
                    . '&csrf=' . $this->getCsrf(),
                [
                    'values' => ['name' => $name],
                ]
            )
        );
        $this->assertTrue(
            in_array('Operation success', $results['messages']['success'], true)
        );
        
        return $results;
    }
}
