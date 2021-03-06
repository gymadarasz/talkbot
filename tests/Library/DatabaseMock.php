<?php declare(strict_types = 1);

/**
 * PHP version 7.4
 *
 * @category  PHP
 * @package   Madsoft\Tests\Library
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */

namespace Madsoft\Tests\Library;

use Madsoft\Library\Database;

/**
 * DatabaseMock
 *
 * @category  PHP
 * @package   Madsoft\Tests\Library
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */
class DatabaseMock extends Database
{
    /**
     * Method getWherePublic
     *
     * @param string   $table        table
     * @param string   $where        where
     * @param string[] $filterUnsafe filterUnsafe
     * @param string   $logic        logic
     *
     * @return string
     */
    public function getWherePublic(
        string $table,
        string $where = '',
        array $filterUnsafe = [],
        string $logic = 'AND'
    ): string {
        return parent::getWhere($table, $where, $filterUnsafe, $logic);
    }
}
