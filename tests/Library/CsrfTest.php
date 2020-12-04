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

use Madsoft\Library\Csrf;
use Madsoft\Library\Session;
use Madsoft\Library\Tester\Test;
use RuntimeException;

/**
 * CsrfTest
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
class CsrfTest extends Test
{
    /**
     * Method testCheckFails
     *
     * @param Csrf    $csrf    csrf
     * @param Session $session session
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     *
     * @suppress PhanUnreferencedPublicMethod
     */
    public function testCheckFails(Csrf $csrf, Session $session): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET['csrf'] = 'asd';
        unset($_SESSION['csrf']);
        try {
            $csrf->check();
            $this->assertTrue(false);
        } catch (RuntimeException $exc) {
            $this->assertTrue(true);
            $this->assertEquals(
                'CSRF token is missing from session.',
                $exc->getMessage()
            );
        }
        
        $session->set('csrf', 'tadaaah');
        $_SERVER['REQUEST_METHOD'] = 'GET';
        unset($_GET['csrf']);
        try {
            $csrf->check();
            $this->assertTrue(false);
        } catch (RuntimeException $exc) {
            $this->assertTrue(true);
            $this->assertEquals(
                'CSRF token is not recieved by request.',
                $exc->getMessage()
            );
        }
    }
    
    /**
     * Method testGetAsFormField
     *
     * @param Csrf $csrf csrf
     *
     * @return void
     *
     * @suppress PhanUnreferencedPublicMethod
     */
    public function testGetAsFormField(Csrf $csrf): void
    {
        $csrfFormField = $csrf->getAsFormField();
        $csrfAsInt = $csrf->get();
        $expectedFormField = '<input type="hidden" name="csrf" value="'
            . $csrfAsInt . '">';
        $this->assertEquals($expectedFormField, $csrfFormField);
    }
}
