<?php declare(strict_types = 1);

/**
 * PHP version 7.4
 *
 * @category  PHP
 * @package   Madsoft\Tests\Library\Account
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */

namespace Madsoft\Tests\Library\Account;

use Madsoft\Library\Invoker;
use Madsoft\Library\Params;
use Madsoft\Library\Tester\Test;

/**
 * ValidatorTest
 *
 * @category  PHP
 * @package   Madsoft\Tests\Library\Account
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
     * Method testValidator
     *
     * @param Invoker $invoker invoker
     *
     * @return void
     *
     * @suppress PhanUnreferencedPublicMethod
     */
    public function testValidator(Invoker $invoker): void
    {
        $validator = new ValidatorMock($invoker);
        $result = $validator->validateLogin(new Params());
        $this->assertEquals([['an error']], $result);
    }
}
