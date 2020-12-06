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

use mysqli;
use mysqli_result;
use RuntimeException;

/**
 * Mysql
 *
 * @category  PHP
 * @package   Madsoft\Library
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */
class Mysql
{
    protected mysqli $mysqli;
    protected bool $connected = false;
    
    protected Config $config;
    protected Transaction $transaction;
    
    /**
     * Method __construct
     *
     * @param Config      $config      config
     * @param Transaction $transaction transaction
     */
    public function __construct(Config $config, Transaction $transaction)
    {
        $this->config = $config;
        $this->transaction = $transaction;
    }
    
    /**
     * Method setMysqli
     *
     * @param mysqli $mysqli mysqli
     *
     * @return self
     */
    public function setMysqli(mysqli $mysqli): self
    {
        $this->mysqli = $mysqli;
        $this->transaction->setMysqli($this->mysqli);
        return $this;
    }
    
    /**
     * Method getTransaction
     *
     * @return Transaction
     */
    public function getTransaction(): Transaction
    {
        $this->connect();
        return $this->transaction;
    }
    
    /**
     * Method connect
     *
     * @return void
     * @throws RuntimeException
     */
    protected function connect(): void
    {
        if ($this->connected) {
            return;
        }
        $dbcfg = $this->config->get('database');
        $this->setMysqli(
            new mysqli(
                $dbcfg->get('host'),
                $dbcfg->get('user'),
                $dbcfg->get('password'),
                $dbcfg->get('database')
            )
        );
        if ($this->mysqli->connect_error) {
            throw new RuntimeException(
                'MySQL connection error: (' . $this->mysqli->connect_errno . ')' .
                    $this->mysqli->connect_error
            );
        }
        $this->connected = true;
    }

    /**
     * Method escape
     *
     * @param mixed $value value
     *
     * @return mixed
     */
    public function escape($value = null)
    {
        if (null === $value || is_int($value) || is_float($value)) {
            return $value;
        }
        if (is_bool($value)) {
            return $value ? 1 : 0;
        }
        if (is_string($value)
            || (is_object($value) && method_exists($value, '__toString'))
        ) {
            $this->connect();
            return $this->mysqli->escape_string((string)$value);
        }
        throw new RuntimeException(
            'Given value can not be converted to string.'
        );
    }
    
    /**
     * Method value
     *
     * @param mixed $value value
     *
     * @return string
     */
    public function value($value = null): string
    {
        return is_string($value) ?
            "'$value'" :
            (null === $value ? 'NULL' : (string)$value);
    }

    /**
     * Method selectOne
     *
     * @param string $query query
     *
     * @return string[]
     * @throws RuntimeException
     */
    public function selectOne(string $query): array
    {
        $result = $this->query($query);
        if ($result instanceof mysqli_result) {
            // return (new Row())->setFields($result->fetch_assoc() ?: []);
            $row = $result->fetch_assoc() ?: [];
            if (!empty($row)) {
                return $row;
            }
            throw new MysqlNotFoundException(
                "Not found results of query:\n$query\n"
            );
        }
        throw new RuntimeException(
            "MySQL query error:\n$query\nMessage: {$this->mysqli->error}"
        );
    }

    /**
     * Method select
     *
     * @param string $query query
     *
     * @return mixed[]
     * @throws RuntimeException
     */
    public function select(string $query): array
    {
        $result = $this->query($query);
        if ($result instanceof mysqli_result) {
            $rows = [];
            while ($row = $result->fetch_assoc()) {
                // $rows[] = (new Row())->setFields($row);
                $rows[] = $row;
            }
            if (!empty($rows)) {
                return $rows;
            }
            throw new MysqlEmptyException(
                "Empty results of query:\n$query\n"
            );
        }
        throw new RuntimeException(
            "MySQL query error:\n$query\nMessage: {$this->mysqli->error}"
        );
    }

    /**
     * Method query
     *
     * @param string $query query
     *
     * @return mixed
     * @throws RuntimeException
     */
    public function query(string $query)
    {
        $this->connect();
        $ret = $this->mysqli->query($query);
        if (false !== $ret) {
            return $ret;
        }
        throw new RuntimeException(
            "MySQL query error:\n$query\nMessage: {$this->mysqli->error}"
        );
    }

    /**
     * Method update
     *
     * @param string $query query
     *
     * @return int
     * @throws RuntimeException
     */
    public function update(string $query): int
    {
        if (false !== $this->query($query) && $this->mysqli->affected_rows) {
            return $this->mysqli->affected_rows;
        }
        throw new MysqlNoAffectException(
            "Not affected by query:\n$query\n"
        );
    }
    
    /**
     * Method delete
     *
     * @param string $query query
     *
     * @return int
     */
    public function delete(string $query): int
    {
        return $this->update($query);
    }

    /**
     * Method insert
     *
     * @param string $query query
     *
     * @return int
     * @throws RuntimeException
     */
    public function insert(string $query): int
    {
        if (false !== $this->query($query) && (int)$this->mysqli->insert_id > 0) {
            return (int)$this->mysqli->insert_id;
        }
        throw new MysqlNoInsertException(
            "Not inserted by query:\n$query\n"
        );
    }
}
