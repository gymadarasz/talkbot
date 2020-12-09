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

use Madsoft\Library\Mysql;
use RuntimeException;

/**
 * Sanitizer
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
class Sanitizer
{
    /**
     * Method sanitizeSql
     *
     * @param mixed[]|object|null $data data
     *
     * @return void
     * @throws RuntimeException
     */
    public function sanitizeSql($data = null): void
    {
        if (null === $data) {
            if (!empty($_GET)) {
                $this->sanitizeSql($_GET);
            }
            if (!empty($_POST)) {
                $this->sanitizeSql($_POST);
            }
            if (!empty($_REQUEST)) {
                $this->sanitizeSql($_REQUEST);
            }
            if (!empty($_SERVER)) {
                $this->sanitizeSql($_SERVER);
            }
            return;
        }
        $this->sanitizeDataSql((array)$data);
    }
    
    /**
     * Method sanitizeDataSql
     *
     * @param mixed[] $data data
     *
     * @return void
     */
    protected function sanitizeDataSql(array $data): void
    {
        foreach ($data as $key => $value) {
            if (preg_match(Mysql::NO_ESC_REGEX, (string)$key)) {
                throw new RuntimeException("Invalid parameter key: '$key'");
            }
            if (is_array($value) || is_object($value)) {
                $this->sanitizeSql($value);
                continue;
            }
            if (is_scalar($value) || is_null($value)) {
                if (preg_match(Mysql::NO_ESC_REGEX, (string)$value)) {
                    throw new RuntimeException("Invalid parameter value: '$value'");
                }
                continue;
            }
            throw new RuntimeException(
                "Invalid parameter type at key '$key': " . gettype($value)
            );
        }
    }
}
