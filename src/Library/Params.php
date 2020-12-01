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

use RuntimeException;

/**
 * Params
 *
 * @category  PHP
 * @package   Madsoft\Library
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 *
 * @SuppressWarnings(PHPMD.Superglobals)
 */
class Params extends Server implements Assoc
{
    /**
     * Method get
     *
     * @param string $key     key
     * @param mixed  $default default
     *
     * @return mixed
     * @throws RuntimeException
     */
    public function get(string $key, $default = null)
    {
        $method = parent::get('REQUEST_METHOD');
        switch ($method) {
        case 'GET':
            if (isset($_GET[$key])) {
                return $_GET[$key];
            }
            break;
        
        case 'POST':
            if (isset($_POST[$key])) {
                return $_POST[$key];
            }
            break;
        default:
            throw new RuntimeException('Incorrect method: "' . $method . '"');
        }
        if (isset($_REQUEST[$key])) {
            return $_REQUEST[$key];
        }
        if (null !== $default) {
            return $default;
        }
        throw new RuntimeException('Parameter not found: "' . $key . '"');
    }
    
    /**
     * Method has
     *
     * @param string $key key
     *
     * @return bool
     * @throws RuntimeException
     */
    public function has(string $key): bool
    {
        $method = parent::get('REQUEST_METHOD');
        switch ($method) {
        case 'GET':
            if (isset($_GET[$key])) {
                return true;
            }
            break;
        
        case 'POST':
            if (isset($_POST[$key])) {
                return true;
            }
            break;
        default:
            throw new RuntimeException('Incorrect method: "' . $method . '"');
        }
        if (isset($_REQUEST[$key])) {
            return true;
        }
        return false;
    }

    /**
     * Method set
     *
     * @param string $key   key
     * @param mixed  $value value
     *
     * @return self
     * @throws RuntimeException
     */
    public function set(string $key, $value): Assoc
    {
        throw new RuntimeException(
            "Parameters are not accessible, "
                . "attempted to override '$key' => '$value'."
        );
    }
}
