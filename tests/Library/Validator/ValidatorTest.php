<?php declare(strict_types = 1);

/**
 * PHP version 7.4
 *
 * @category  PHP
 * @package   Madsoft\Tests\Library\Validator
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */

namespace Madsoft\Tests\Library\Validator;

use Madsoft\Library\Invoker;
use Madsoft\Library\Tester\Test;
use Madsoft\Library\Validator\Validator;

/**
 * ValidatorTest
 *
 * @category  PHP
 * @package   Madsoft\Tests\Library\Validator
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 *
 * @suppress PhanUnreferencedClass
 */
class ValidatorTest extends Test
{
    /**
     * Method testGetErrors
     *
     * @param Invoker $invoker invoker
     *
     * @return void
     *
     * @suppress PhanUnreferencedPublicMethod
     */
    public function testGetErrors(Invoker $invoker): void
    {
        $validator = new Validator($invoker);
        $errors = $validator->getErrors([]);
        $this->assertEquals([], $errors);
    }
    
    /**
     * Method testGetFirstError
     *
     * @param Invoker $invoker invoker
     *
     * @return void
     *
     * @suppress PhanUnreferencedPublicMethod
     */
    public function testGetFirstError(Invoker $invoker): void
    {
        $validator = new Validator($invoker);
        $error = $validator->getFirstError([]);
        $this->assertEquals([], $error);
    }
}
