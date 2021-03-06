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
class Params extends Sanitizer implements Assoc
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
    protected Merger $merger;




    /**
     * Method __construct
     *
     * @param Server $server server
     * @param Merger $merger merger
     */
    public function __construct(
        Server $server,
        Merger $merger
    ) {
        $this->server = $server;
        $this->merger = $merger;
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
     * Method addOverrides
     *
     * @param mixed[] $overrides overrides
     *
     * @return self
     */
    public function addOverrides(array $overrides): self
    {
        $this->overrides = $this->merger->merge($this->overrides, $overrides);
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
        $overrides = [];
        $overrided = false;
        if (array_key_exists($key, $this->overrides)) {
            if (is_scalar($this->overrides[$key])) {
                return $this->overrides[$key];
            }
            $overrides = (array)$this->overrides[$key];
            $overrided = true;
        }
        $method = $this->server->get('REQUEST_METHOD');
        switch ($method) {
        case 'GET':
            if (array_key_exists($key, $_GET)) {
                return $this->getOverridedGet($overrides, $key);
            }
            break;

        case 'POST':
            if (array_key_exists($key, $_POST)) {
                return $this->getOverridedPost($overrides, $key);
            }
            break;
        default:
            throw new RuntimeException('Incorrect method: "' . $method . '"');
        }
        if (array_key_exists($key, $_REQUEST)) {
            return $this->getOverridedRequest($overrides, $key);
        }
        if ($overrided) {
            return $overrides;
        }
        return $this->getDefaultValue($key, $default);
    }
    
    /**
     * Method getOverridedGet
     *
     * @param mixed[] $overrides overrides
     * @param string  $key       key
     *
     * @return mixed
     */
    protected function getOverridedGet(array $overrides, string $key)
    {
        if ($overrides) {
            if (is_array($_GET[$key])) {
                return $this->merger->merge(
                    $this->merger->merge($this->defaults, $_GET)[$key],
                    $overrides
                );
            }
            return $overrides;
        }
        return $_GET[$key];
    }
    
    /**
     * Method getOverridedPost
     *
     * @param mixed[] $overrides overrides
     * @param string  $key       key
     *
     * @return mixed
     */
    protected function getOverridedPost(array $overrides, string $key)
    {
        if ($overrides) {
            if (is_array($_POST[$key])) {
                return $this->merger->merge(
                    $this->merger->merge($this->defaults, $_POST)[$key],
                    $overrides
                );
            }
            return $overrides;
        }
        return $_POST[$key];
    }
    
    /**
     * Method getOverridedRequest
     *
     * @param mixed[] $overrides overrides
     * @param string  $key       key
     *
     * @return mixed
     */
    protected function getOverridedRequest(array $overrides, string $key)
    {
        if ($overrides) {
            if (is_array($_REQUEST[$key])) {
                return $this->merger->merge(
                    $this->merger->merge($this->defaults, $_REQUEST)[$key],
                    $overrides
                );
            }
            return $overrides;
        }
        return $_REQUEST[$key];
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
        if (array_key_exists($key, $this->defaults)
            && null !== $this->defaults[$key]
        ) {
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
        if (array_key_exists($key, $this->overrides)) {
            return true;
        }
        $method = $this->server->get('REQUEST_METHOD');
        switch ($method) {
        case 'GET':
            if (array_key_exists($key, $_GET)) {
                return true;
            }
            break;

        case 'POST':
            if (array_key_exists($key, $_POST)) {
                return true;
            }
            break;
        default:
            throw new RuntimeException('Incorrect method: "' . $method . '"');
        }
        if (array_key_exists($key, $_REQUEST)) {
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
        if (array_key_exists($key, $this->defaults)
            && null !== $this->defaults[$key]
        ) {
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
