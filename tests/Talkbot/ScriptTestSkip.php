<?php declare(strict_types = 1);

/**
 * PHP version 7.4
 *
 * @category  PHP
 * @package   Madsoft\Talkbot
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */

namespace Madsoft\Talkbot;

use Madsoft\Tests\Library\Crud\ContentTest;

/**
 * ScriptTest
 *
 * @category  PHP
 * @package   Madsoft\Talkbot
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */
class ScriptTestSkip extends ContentTest
{
    /**
     * Variable $routes
     *
     * @var string[]
     */
    protected array $routes = [
        __DIR__ . '/../../src/Library/Account/account.api.routes.php',
        __DIR__ . '/../../src/Library/Crud/content.api.routes.php',
        __DIR__ . '/../../src/Talkbot/script.api.routes.php',
    ];
    
    /**
     * Method beforeAll
     *
     * @return void
     */
    public function beforeAll(): void
    {
        $this->database->delRows('script');
        parent::beforeAll();
    }
    
    /**
     * Method afterAll
     *
     * @return void
     */
    public function afterAll(): void
    {
        $this->database->delRows('script');
        parent::afterAll();
    }
    
    /**
     * Method testScriptList
     *
     * @return void
     *
     * @suppress PhanUnreferencedPublicMethod
     */
    public function testScriptList(): void
    {
        $results = $this->json->decode(
            $this->post(
                'q=content/create&csrf=' . $this->getCsrf(),
                [
                    'values' => [
                        'name' => 'test1 private',
                    ],
                ]
            )
        );
        $this->assertTrue(
            in_array('Operation success', $results['messages']['success'], true)
        );
        $cid = $results['insertId'];
        
        $results = $this->json->decode(
            $this->get(
                'q=script/list&content_id=' . $cid
                    . '&csrf=' . $this->getCsrf()
            )
        );
        $this->assertTrue(
            in_array('Empty list', $results['messages']['error'], true)
        );
        
        $results = $this->json->decode(
            $this->post(
                'q=my-contents/create&csrf=' . $this->getCsrf(),
                [
                    'values' => [
                        'content_id' => $cid,
                        'talks' => 'robot',
                        'text' => 'test1 message from robot',
                    ]
                ]
            )
        );
        $this->assertTrue(
            in_array('Operation success', $results['messages']['success'], true)
        );
    }
}
