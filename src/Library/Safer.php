<?php declare(strict_types = 1);

/**
 * PHP version 7.4
 *
 * @category  PHP
 * @package   Madsoft\Library
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */

namespace Madsoft\Library;

/**
 * Safer
 *
 * @category  PHP
 * @package   Madsoft\Library
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */
class Safer
{
    
    // mysqli_real_escape_string
    // htmlentities
    
    /**
     * Method free
     *
     * @param callable $callback callback
     * @param mixed    $value    value
     *
     * @return mixed
     */
    public function freez(callable $callback, $value)
    {
        if (is_array($value) || is_object($value)) {
            $ret = [];
            foreach ((array)$value as $key => $value) {
                $ret[$this->freez($callback, $key)] = $this->freez(
                    $callback,
                    $value
                );
            }
            return $ret;
        }
        $ret = $callback((string)$value);
        return $ret;
    }
}
