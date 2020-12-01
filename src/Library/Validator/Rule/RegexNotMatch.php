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
use RuntimeException;

/**
 * RegexNotMatch
 *
 * @category  PHP
 * @package   Madsoft\Library\Validator\Rule
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */
abstract class RegexNotMatch extends Rule
{
    const MESSAGE = "Incorrect format";
    const PATTERN = '//';
    
    /**
     * Method check
     *
     * @param mixed $value value
     *
     * @return bool
     */
    public function check($value): bool
    {
        $results = preg_match($this::PATTERN, $value);
        if (false === $results) {
            throw new RuntimeException('Regexp matching error');
        }
        return !$results;
    }
}
