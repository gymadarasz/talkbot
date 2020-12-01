<?php declare(strict_types = 1);

/**
 * PHP version 7.4
 *
 * @category  PHP
 * @package   Madsoft\Library\Validator\Rule
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */

namespace Madsoft\Library\Validator\Rule;

use Madsoft\Library\Validator\Rule;

/**
 * Password
 *
 * @category  PHP
 * @package   Madsoft\Library\Validator\Rule
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */
class Password extends Rule
{
    const MESSAGE = "Invalid password";
    
    protected bool $checkMinLength = true;
    protected bool $checkHasLower = true;
    protected bool $checkHasUpper = true;
    protected bool $checkHasNumber = true;
    protected bool $checkHasSpecChar = true;
    
    protected MinLength $minLength;
    protected HasLower $hasLower;
    protected HasUpper $hasUpper;
    protected HasNumber $hasNumber;
    protected HasSpecChar $hasSpecChar;

    /**
     * Method __construct
     *
     * @param MinLength   $minLength   minLength
     * @param HasLower    $hasLower    hasLower
     * @param HasUpper    $hasUpper    hasUpper
     * @param HasNumber   $hasNumber   hasNumber
     * @param HasSpecChar $hasSpecChar hasSpecChar
     */
    public function __construct(
        MinLength $minLength,
        HasLower $hasLower,
        HasUpper $hasUpper,
        HasNumber $hasNumber,
        HasSpecChar $hasSpecChar
    ) {
        $this->minLength = $minLength;
        $this->hasLower = $hasLower;
        $this->hasUpper = $hasUpper;
        $this->hasNumber = $hasNumber;
        $this->hasSpecChar = $hasSpecChar;
    }
    
    /**
     * Method check
     *
     * @param mixed $value value
     *
     * @return bool
     */
    public function check($value): bool
    {
        if (!$this->checkBasic((string)$value)) {
            return false;
        }
        if (!$this->checkMedium((string)$value)) {
            return false;
        }
        if (!$this->checkComplex((string)$value)) {
            return false;
        }
        return true;
    }
    
    /**
     * Method checkBasic
     *
     * @param string $value value
     *
     * @return boolean
     */
    protected function checkBasic(string $value)
    {
        if ($this->checkMinLength && !$this->minLength->check($value)) {
            return false;
        }
        return true;
    }
    
    /**
     * Method checkMedium
     *
     * @param string $value value
     *
     * @return boolean
     */
    protected function checkMedium(string $value)
    {
        if ($this->checkHasLower && !$this->hasLower->check($value)) {
            return false;
        }
        if ($this->checkHasUpper && !$this->hasUpper->check($value)) {
            return false;
        }
        return true;
    }
    
    /**
     * Method checkComplex
     *
     * @param string $value value
     *
     * @return boolean
     */
    protected function checkComplex(string $value)
    {
        if ($this->checkHasNumber && !$this->hasNumber->check($value)) {
            return false;
        }
        if ($this->checkHasSpecChar && !$this->hasSpecChar->check($value)) {
            return false;
        }
        return true;
    }
}
