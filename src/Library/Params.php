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
class Params implements Assoc
{
    /**
     * Variable $defaults
     *
     * @var mixed[]
     */
    protected array $defaults = [];
    
    /**
     * Variable $overrides
     *
     * @var mixed[]
     */
    protected array $overrides = [];
    
    protected Server $server;
    
    /**
     * Method __construct
     *
     * @param Server $server server
     */
    public function __construct(Server $server)
    {
        $this->server = $server;
    }
    
    /**
     * Method setOverrides
     *
     * @param mixed[] $overrides overrides
     *
     * @return self
     */
    public function setOverrides(array $overrides): self
    {
        $this->overrides = $overrides;
        return $this;
    }
    
    /**
     * Method setDefaults
     *
     * @param mixed[] $defaults defaults
     *
     * @return self
     */
    public function setDefaults(array $defaults): self
    {
        $this->defaults = $defaults;
        return $this;
    }
    
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
        if (in_array($key, array_keys($this->overrides), true)) {
            return $this->overrides[$key];
        }
        $method = $this->server->get('REQUEST_METHOD');
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
        return $this->getDefaultValue($key, $default);
    }
    
    /**
     * Method getDefaultValue
     *
     * @param string $key     key
     * @param mixed  $default default
     *
     * @return mixed
     * @throws RuntimeException
     */
    protected function getDefaultValue(string $key, $default = null)
    {
        if (null !== $default) {
            return $default;
        }
        if (isset($this->defaults[$key]) && null !== $this->defaults[$key]) {
            return $this->defaults[$key];
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
        if (in_array($key, array_keys($this->overrides), true)) {
            return true;
        }
        $method = $this->server->get('REQUEST_METHOD');
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
        return $this->hasDefaultValue($key);
    }
    
    /**
     * Method hasDefaultValue
     *
     * @param string $key key
     *
     * @return bool
     */
    protected function hasDefaultValue(string $key): bool
    {
        if (isset($this->defaults[$key]) && null !== $this->defaults[$key]) {
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
