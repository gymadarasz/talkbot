<?php declare(strict_types = 1);

/**
 * PHP version 7.4
 *
 * @category  PHP
 * @package   Madsoft\Tests\Library\Validator\Rule
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */

namespace Madsoft\Tests\Library\Validator\Rule;

use Madsoft\Library\Tester\Test;
use Madsoft\Library\Validator\Rule\HasLower;
use Madsoft\Library\Validator\Rule\HasNumber;
use Madsoft\Library\Validator\Rule\HasSpecChar;
use Madsoft\Library\Validator\Rule\HasUpper;
use Madsoft\Library\Validator\Rule\StringRule;

/**
 * RulesTest
 *
 * @category  PHP
 * @package   Madsoft\Tests\Library\Validator\Rule
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 *
 * @suppress PhanUnreferencedClass
 */
class RulesTest extends Test
{
    /**
     * Method testMatch
     *
     * @return void
     *
     * @suppress PhanUnreferencedPublicMethod
     */
    public function testMatch(): void
    {
        $match = new MatchMock();
        $match->setEqualTo('foo');
        $this->assertFalse($match->check('bar'));
        $this->assertTrue($match->check('foo'));
    }
    
    /**
     * Method testMinLength
     *
     * @return void
     *
     * @suppress PhanUnreferencedPublicMethod
     */
    public function testMinLength(): void
    {
        $minLength = new MinLengthMock();
        $minLength->setMin(3);
        $this->assertFalse($minLength->check('ab'));
        $this->assertTrue($minLength->check('abc'));
    }
    
    /**
     * Method testPassword
     *
     * @return void
     *
     * @suppress PhanUnreferencedPublicMethod
     */
    public function testPassword(): void
    {
        $minLength = new MinLengthMock();
        $minLength->setMin(10);
        $hasLower = new HasLower();
        $hasUpper = new HasUpper();
        $hasNumber = new HasNumber();
        $hasSpecChar = new HasSpecChar();
        $password = new PasswordMock(
            $minLength,
            $hasLower,
            $hasUpper,
            $hasNumber,
            $hasSpecChar
        );
        $password
            ->setCheckMinLength(true)
            ->setCheckHasLower(true)
            ->setCheckHasUpper(true)
            ->setCheckHasNumber(true)
            ->setCheckHasSpecChar(true);
        $this->assertFalse($password->check('simple'));
        $this->assertTrue($password->check('SoComplexPass1234!@#$'));
    }
    
    /**
     * Method testPasswordVerify
     *
     * @return void
     *
     * @suppress PhanUnreferencedPublicMethod
     */
    public function testPasswordVerify(): void
    {
        $password = '123';
        $stringRule = new StringRule();
        $passwordVerify = new PasswordVerifyMock($stringRule);
        $passwordVerify->setPassword($password);
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $this->assertTrue(false !== $hash);
        $this->assertFalse($passwordVerify->check('wrong'));
        $this->assertTrue($passwordVerify->check($hash));
    }
}
