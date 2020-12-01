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
use Madsoft\Library\Csrf;
use Madsoft\Library\Invoker;
use Madsoft\Library\Merger;
use Madsoft\Library\Params;
use Madsoft\Library\Safer;
use Madsoft\Library\Session;
use Madsoft\Library\Template;
use Madsoft\Library\Tester\Test;
use RuntimeException;

/**
 * TemplateTest
 *
 * @category  PHP
 * @package   Madsoft\Tests\Library
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */
class TemplateTest extends Test
{
    /**
     * Method testTemplate
     *
     * @param Invoker $invoker invoker
     *
     * @return void
     *
     * @suppress PhanUnreferencedPublicMethod
     */
    public function testTemplate(Invoker $invoker): void
    {
        $template = new Template(
            new Config(new Invoker(), new Merger()),
            new Safer(),
            $invoker->getInstance(Csrf::class)
        );
        $msg = '';
        
        try {
            $template->process(
                __DIR__ . '/test.phtml',
                ['foo']
            );
        } catch (RuntimeException $exception) {
            $msg = $exception->getMessage();
        }
        $this->assertEquals("Variable name can not be number: '0'", $msg);
        
        try {
            $template->setHtmlViewTemplate(false)->process(
                __DIR__ . '/test.phtml',
                ['safer' => 'never!'],
                null
            );
        } catch (RuntimeException $exception) {
            $msg = $exception->getMessage();
        }
        $this->assertEquals("Variable name is reserved: 'safer'", $msg);
        
        $results = $template->process(
            __DIR__ . '/test.phtml',
            ['data1' => 'foo'],
            ''
        );
        $this->assertEquals('Hello Template foo!', $results);
        $csrf = $template->getVars()['csrf'];
        $this->assertTrue((bool)$csrf);
        
        $template = new Template(
            new Config(new Invoker(), new Merger()),
            new Safer(),
            new Csrf(new Session(), new Params())
        );
        $results = $template->process(
            __DIR__ . '/test.phtml',
            ['data1' => 'bazz'],
            ''
        );
        $this->assertEquals('Hello Template bazz!', $results);
        $this->assertNotEquals($csrf, $template->getVars()['csrf']);
        $csrf = $template->getVars()['csrf'];
        $this->assertTrue((bool)$csrf);
        
        $template->restrict();
    }
}
