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

use Madsoft\Library\Validator\Rule\Password;

/**
 * PasswordMock
 *
 * @category  PHP
 * @package   Madsoft\Tests\Library\Validator\Rule
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */
class PasswordMock extends Password
{
    /**
     * Method setCheckMinLength
     *
     * @param bool $checkMinLength checkMinLength
     *
     * @return self
     */
    public function setCheckMinLength(bool $checkMinLength): self
    {
        $this->checkMinLength = $checkMinLength;
        return $this;
    }
    
    /**
     * Method setCheckHasLower
     *
     * @param bool $checkHasLower checkHasLower
     *
     * @return self
     */
    public function setCheckHasLower(bool $checkHasLower): self
    {
        $this->checkHasLower = $checkHasLower;
        return $this;
    }
    
    /**
     * Method setCheckHasUpper
     *
     * @param bool $checkHasUpper checkHasUpper
     *
     * @return self
     */
    public function setCheckHasUpper(bool $checkHasUpper): self
    {
        $this->checkHasUpper = $checkHasUpper;
        return $this;
    }
    
    /**
     * Method setCheckHasNumber
     *
     * @param bool $checkHasNumber checkHasNumber
     *
     * @return self
     */
    public function setCheckHasNumber(bool $checkHasNumber): self
    {
        $this->checkHasNumber = $checkHasNumber;
        return $this;
    }
    
    /**
     * Method setCheckHasSpecChar
     *
     * @param bool $checkHasSpecChar checkHasSpecChar
     *
     * @return self
     */
    public function setCheckHasSpecChar(bool $checkHasSpecChar): self
    {
        $this->checkHasSpecChar = $checkHasSpecChar;
        return $this;
    }
}
