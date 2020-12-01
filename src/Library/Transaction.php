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
use RuntimeException;

/**
 * Transaction
 *
 * @category  PHP
 * @package   Madsoft\Library
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */
class Transaction
{
    protected mysqli $mysqli;
    
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
        return $this;
    }

    /**
     * Method start
     *
     * @return void
     * @throws RuntimeException
     */
    public function start(): void
    {
        if (!$this->mysqli->autocommit(false)) {
            throw new RuntimeException('Mysql transaction start failed.');
        }
    }
    
    /**
     * Method commit
     *
     * @return void
     * @throws RuntimeException
     */
    public function commit(): void
    {
        if (!$this->mysqli->commit()) {
            throw new RuntimeException('Mysql transaction commit failed.');
        }
        $this->stop();
    }
    
    
    /**
     * Method rollback
     *
     * @return void
     * @throws RuntimeException
     */
    public function rollback(): void
    {
        if (!$this->mysqli->rollback()) {
            throw new RuntimeException('Mysql transaction rollback failed.');
        }
        $this->stop();
    }
    
    /**
     * Method stop
     *
     * @return void
     * @throws RuntimeException
     */
    protected function stop(): void
    {
        if (!$this->mysqli->autocommit(true)) {
            throw new RuntimeException('Mysql transaction stop failed.');
        }
    }
}
