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
use Madsoft\Library\Merger;
use Madsoft\Library\Params;
use Madsoft\Library\Server;
use Madsoft\Library\Tester\Test;
use Madsoft\Tests\Library\Account\ValidatorMock;

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
        $result = $validator->validateLogin(new Params(new Server(), new Merger()));
        $this->assertEquals([['an error']], $result);
    }
}
