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
 * Session
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
class Session implements Assoc
{
    /**
     * Method __construct
     */
    public function __construct()
    {
        $this->start();
    }
    /**
     * Method start
     *
     * @return bool
     */
    public function start(): bool
    {
        $ret = false;
        if (session_status() == PHP_SESSION_NONE) {
            if (php_sapi_name() === 'cli') {
                if (!isset($_SESSION)) {
                    $_SESSION = [];
                    return true;
                }
                return false;
            }
            $ret = session_start();
        }
        return $ret;
    }
    
    /**
     * Method destroy
     *
     * @return bool
     */
    public function destroy(): bool
    {
        foreach (array_keys($_SESSION) as $key) {
            unset($_SESSION[$key]);
        }
        if (php_sapi_name() === 'cli') {
            unset($_SESSION);
            return true;
        }
        return session_destroy();
    }
    
    /**
     * Method get
     *
     * @param string $key     key
     * @param mixed  $default default
     *
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }
    
    /**
     * Method set
     *
     * @param string $key   key
     * @param mixed  $value value
     *
     * @return Assoc
     */
    public function set(string $key, $value): Assoc
    {
        $_SESSION[$key] = $value;
        return $this;
    }
    
    /**
     * Method has
     *
     * @param string $key key
     *
     * @return bool
     */
    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }
    
    /**
     * Method unset
     *
     * @param string $key key
     *
     * @return self
     */
    public function unset(string $key): self
    {
        unset($_SESSION[$key]);
        return $this;
    }
}
