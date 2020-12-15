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

use Madsoft\Library\Validator\Rule\StringRule;
use RuntimeException;

/**
 * Server
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
class Server implements Assoc
{
    
    /**
     * Method getMethod
     *
     * @return string
     */
    public function getMethod(): string
    {
        return $this->get('REQUEST_METHOD');
    }
    
    /**
     * Method getBaseUrl
     *
     * @return string
     */
    public function getBaseUrl(): string
    {
        return sprintf(
            "%s://%s",
            $this->has('HTTPS') && $this->get('HTTPS') != 'off' ?
                'https' :
                'http',
            $this->get('SERVER_NAME')
        );
    }
    
    /**
     * Method getBaseUri
     *
     * @return string
     */
    public function getBaseUri(): string
    {
        return sprintf(
            "%s://%s%s",
            $this->has('HTTPS') && $this->get('HTTPS') != 'off' ?
                'https' :
                'http',
            $this->get('SERVER_NAME'),
            $this->get('REQUEST_URI')
        );
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
        return $_SERVER[$key] ?? $default;
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
        return array_key_exists($key, $_SERVER);
    }

    /**
     * Method set
     *
     * @param string $key   key
     * @param mixed  $value value
     *
     * @return Assoc
     * @throws RuntimeException
     */
    public function set(string $key, $value): Assoc
    {
        throw new RuntimeException(
            '$_SERVER Superglobals is not writeable. '
                . 'Trying to access to $_SERVER at key: ' . $key
                . ' Value: ' . ((new StringRule())->check($value) ? $value : '???')
        );
    }
}
