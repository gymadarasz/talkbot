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
 * Match
 *
 * @category  PHP
 * @package   Madsoft\Library\Validator\Rule
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */
class Match extends Rule
{
    const MESSAGE = "Doesn't match";
    
    /**
     * Variable $equalTo
     *
     * @var mixed
     */
    protected $equalTo;
    
    /**
     * Method check
     *
     * @param mixed $value value
     *
     * @return bool
     */
    public function check($value): bool
    {
        return $this->equalTo === $value;
    }
}
