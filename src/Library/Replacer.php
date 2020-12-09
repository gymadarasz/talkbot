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
use function count;

/**
 * Replacer
 *
 * @category  PHP
 * @package   Madsoft\Library
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */
class Replacer
{
    protected ?Mysql $mysql = null;
    
    protected Invoker $invoker;
    
    /**
     * Method __construct
     *
     * @param Invoker $invoker invoker
     */
    public function __construct(Invoker $invoker)
    {
        $this->invoker = $invoker;
    }
    
    /**
     * Method replace
     *
     * @param string  $value  value
     * @param Assoc[] $assocs assocs
     *
     * @return string
     * @throws RuntimeException
     */
    public function replace(string $value, array $assocs): string
    {
        $matches = null;
        if (preg_match_all(
            '/\{\{\s*([a-zA-Z0-9_]+)\s*\:\s*([a-zA-Z0-9_][a-zA-Z0-9_\-\.]*)\s*\}\}/',
            $value,
            $matches
        )
        ) {
            foreach ($matches[0] as $key => $match) {
                $assocKey = $matches[1][$key];
                $assocValue = explode('.', $matches[2][$key]);
                $count = count($assocValue);
                if ($count === 1) {
                    $value = str_replace(
                        $matches[0][$key],
                        $this->getMysql()->escape(
                            $assocs[$assocKey]->get($assocValue[0])
                        ),
                        $value
                    );
                    continue;
                }
                if ($count === 2) {
                    if (!isset($assocs[$assocKey])) {
                        throw new RuntimeException(
                            'Associative content missing at key: "' . $assocKey . '"'
                                . ', possible keys: "'
                                . implode('", "', array_keys($assocs)) . '"'
                        );
                    }
                    $field = $assocs[$assocKey]->get($assocValue[0], []);
                    if (!is_array($field)) {
                        throw new RuntimeException(
                            'Incorrect field replacement: "'
                                . $assocValue[0] . '" expected to be an array, '
                                . gettype($field) . ' given.'
                                . (is_scalar($field) ? ' (' . $field . ')' : '')
                        );
                    }
                    if (!isset($field[$assocValue[1]])) {
                        throw new RuntimeException(
                            'Missing field replacement: "' . $match . '"'
                        );
                    }
                    $replace = $field[$assocValue[1]] ?? '';
                    $value = str_replace(
                        $matches[0][$key],
                        $this->getMysql()->escape($replace),
                        $value
                    );
                    continue;
                }
                throw new RuntimeException(
                    'Incorrect replacement value: "'
                        . $match . '"'
                );
            }
        }
        return $value;
    }
    
    /**
     * Method replaceAll
     *
     * @param mixed[] $data   data
     * @param Assoc[] $assocs assocs
     *
     * @return mixed[]
     */
    public function replaceAll(array $data, array $assocs): array
    {
        $ret = [];
        foreach ($data as $key => $value) {
            if (is_scalar($value)) {
                $ret[$this->replace((string)$key, $assocs)] = $this->replace(
                    (string)$value,
                    $assocs
                );
                continue;
            }
            if (is_array($value) || is_object($value)) {
                $ret[$this->replace((string)$key, $assocs)] = $this->replaceAll(
                    (array)$value,
                    $assocs
                );
                continue;
            }
            throw new RuntimeException(
                'Replacement possible only on scalar or scalar arrays, '
                    . gettype($value) . ' given'
            );
        }
        return $ret;
    }
    
    /**
     * Method getMysql
     *
     * @return Mysql
     */
    protected function getMysql(): Mysql
    {
        if (null === $this->mysql) {
            $this->mysql = $this->invoker->getInstance(Mysql::class);
        }
        return $this->mysql;
    }
}
