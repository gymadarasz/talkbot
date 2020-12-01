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
 * HasLetter
 *
 * @category  PHP
 * @package   Madsoft\Library\Validator\Rule
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */
class HasLetter extends Rule
{
    const MESSAGE = "Doesn't contains any letter";
    
    protected HasLower $hasLower;
    protected HasUpper $hasUpper;
    
    /**
     * Method __construct
     *
     * @param HasLower $hasLower hasLower
     * @param HasUpper $hasUpper hasUpper
     */
    public function __construct(HasLower $hasLower, HasUpper $hasUpper)
    {
        $this->hasLower = $hasLower;
        $this->hasUpper = $hasUpper;
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
        return $this->hasLower->check($value) || $this->hasUpper->check($value);
    }
}
