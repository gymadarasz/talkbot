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
 * PasswordVerify
 *
 * @category  PHP
 * @package   Madsoft\Library\Validator\Rule
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */
class PasswordVerify extends Rule
{
    const MESSAGE = "Password isn't match";
    
    protected string $password = '';
    
    protected StringRule $stringRule;
    
    /**
     * Method __construct
     *
     * @param StringRule $stringRule stringRule
     */
    public function __construct(StringRule $stringRule)
    {
        $this->stringRule = $stringRule;
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
        if ($this->stringRule->check($value)) {
            return password_verify($this->password, (string)$value);
        }
        return false;
    }
}
