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
 * Enum
 *
 * @category  PHP
 * @package   Madsoft\Library\Validator\Rule
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */
class Enum extends Rule
{
    const MESSAGE = 'Invalid value';
    
    /**
     * Variable $values
     *
     * @var string[]
     */
    protected array $values = [];
    
    /**
     * Method check
     *
     * @param mixed $value value
     *
     * @return bool
     */
    public function check($value): bool
    {
        return in_array($value, $this->values, true);
    }
}
