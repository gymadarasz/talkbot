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

use Madsoft\Library\Session;
use Madsoft\Library\Tester\Test;

/**
 * SessionTest
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
class SessionTest extends Test
{
    /**
     * Method testSession
     *
     * @param Session $session session
     *
     * @return void
     *
     * @suppress PhanUnreferencedPublicMethod
     */
    public function testSession(Session $session): void
    {
        $session->set('foo', 'bar');
        $this->assertEquals('bar', $session->get('foo', 'bazz'));
        $session->destroy();
        $this->assertEquals('bazz', $session->get('foo', 'bazz'));
    }
}
