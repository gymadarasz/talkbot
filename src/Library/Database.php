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
 * Database
 *
 * @category  PHP
 * @package   Madsoft\Library
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */
class Database
{
    const LOGICS = ['AND', 'OR'];
    const NO_CONDITION_FILTERS = [
        'AND' => '1=0',
        'OR' => '1=1',
    ];
    
    protected Safer $safer;
    protected Mysql $mysql;

    /**
     * Method __construct
     *
     * @param Safer $safer safer
     * @param Mysql $mysql mysql
     */
    public function __construct(
        Safer $safer,
        Mysql $mysql
    ) {
        $this->safer = $safer;
        $this->mysql = $mysql;
    }
    
    /**
     * Method getRow
     *
     * @param string   $tableUnsafe  tableUnsafe
     * @param string[] $fieldsUnsafe fieldsUnsafe
     * @param string   $where        where
     * @param mixed[]  $filterUnsafe filterUnsafe
     * @param string   $filterLogic  filterLogic
     *
     * @return string[]
     */
    public function getRow(
        string $tableUnsafe,
        array $fieldsUnsafe,
        string $where = '',
        array $filterUnsafe = [],
        string $filterLogic = 'AND'
    ): array {
        return $this->get(
            $tableUnsafe,
            $fieldsUnsafe,
            $where,
            $filterUnsafe,
            $filterLogic,
            1,
            0
        );
    }
    
    /**
     * Method getRows
     *
     * @param string   $tableUnsafe  tableUnsafe
     * @param string[] $fieldsUnsafe fieldsUnsafe
     * @param string   $where        where
     * @param mixed[]  $filterUnsafe filterUnsafe
     * @param string   $filterLogic  filterLogic
     * @param int      $limit        limit
     * @param int      $offset       offset
     *
     * @return string[][]
     */
    public function getRows(
        string $tableUnsafe,
        array $fieldsUnsafe,
        string $where = '',
        array $filterUnsafe = [],
        string $filterLogic = 'AND',
        int $limit = 0,
        int $offset = 0
    ): array {
        return $this->get(
            $tableUnsafe,
            $fieldsUnsafe,
            $where,
            $filterUnsafe,
            $filterLogic,
            $limit,
            $offset
        );
    }
    
    /**
     * Method row
     *
     * @param string   $tableUnsafe  tableUnsafe
     * @param string[] $fieldsUnsafe fieldsUnsafe
     * @param string   $where        where
     * @param mixed[]  $filterUnsafe filterUnsafe
     * @param string   $filterLogic  filterLogic
     * @param int      $limit        limit
     * @param int      $offset       offset
     *
     * @return mixed[]
     */
    protected function get(
        string $tableUnsafe,
        array $fieldsUnsafe,
        string $where = '',
        array $filterUnsafe = [],
        string $filterLogic = 'AND',
        int $limit = 1,
        int $offset = 0
    ): array {
        $table = $this->mysql->escape($tableUnsafe);
        $mysql = $this->mysql;
        $fields = implode(
            ', ',
            $this->safer->freez(
                static function ($value) use ($mysql, $table) {
                    return "`$table`.`" . $mysql->escape($value) . "`";
                },
                $fieldsUnsafe
            )
        );
        $query = "SELECT $fields FROM `$table`"
            . $this->getWhere($table, $where, $filterUnsafe, $filterLogic);
        if ($limit >= 1) {
            $query .= " LIMIT $offset, $limit";
        }
        if ($limit === 1) {
            return $this->mysql->selectOne($query);
        }
        return $this->mysql->select($query);
    }
    
    /**
     * Method getWhere
     *
     * @param string  $table        table
     * @param string  $where        where
     * @param mixed[] $filterUnsafe filterUnsafe
     * @param string  $logic        logic
     *
     * @return string
     * @throws RuntimeException
     */
    protected function getWhere(
        string $table,
        string $where = '',
        array $filterUnsafe = [],
        string $logic = 'AND'
    ): string {
        $filter = $this->safer->freez([$this->mysql, 'escape'], $filterUnsafe);
        if (!in_array($logic, self::LOGICS, true)) {
            throw new RuntimeException("Invalid logic: '$logic'");
        }
        $query = '';
        if ($filter) {
            $query .= " WHERE " . $this->getConditions(
                $table,
                $where,
                $filter,
                $logic
            );
        }
        return $query;
    }
    
    /**
     * Method getConditions
     *
     * @param string  $table  table
     * @param string  $where  where
     * @param mixed[] $filter filter
     * @param string  $logic  logic
     *
     * @return string
     */
    protected function getConditions(
        string $table,
        string $where = '',
        array $filter = [],
        string $logic = 'AND'
    ): string {
        $conds = [];
        foreach ($filter as $key => $value) {
            $conds[] = "`$table`.`$key` = " . $this->mysql->value($value);
        }
        
        $ret = $conds ?
            implode(" $logic ", $conds) :
            $this::NO_CONDITION_FILTERS[$logic];
        if (trim($where)) {
            $ret = "($ret) $where";
        }
        return $ret;
    }
    
    /**
     * Method addRow
     *
     * @param string  $tableUnsafe  tableUnsafe
     * @param mixed[] $valuesUnsafe valuesUnsafe
     *
     * @return int
     */
    public function addRow(string $tableUnsafe, array $valuesUnsafe): int
    {
        return $this->add($tableUnsafe, $valuesUnsafe);
    }
    
    /**
     * Method add
     *
     * @param string  $tableUnsafe  tableUnsafe
     * @param mixed[] $valuesUnsafe valuesUnsafe
     *
     * @return int
     */
    protected function add(
        string $tableUnsafe,
        array $valuesUnsafe
    ): int {
        $table = $this->mysql->escape($tableUnsafe);
        $fields = $this->safer->freez([$this->mysql, 'escape'], $valuesUnsafe);
        $keys = implode('`, `', array_keys($fields));
        foreach ($fields as &$field) {
            $field = $this->mysql->value($field);
        }
        $values = implode(", ", $fields);
        $query = "INSERT INTO `$table` (`$keys`) VALUES ($values)";
        return $this->mysql->insert($query);
    }
    
    /**
     * Method delRow
     *
     * @param string  $tableUnsafe  tableUnsafe
     * @param string  $where        where
     * @param mixed[] $filterUnsafe filterUnsafe
     * @param string  $filterLogic  filterLogic
     * @param int     $limit        limit
     *
     * @return int
     */
    public function delRow(
        string $tableUnsafe,
        string $where = '',
        array $filterUnsafe = [],
        string $filterLogic = 'AND',
        int $limit = 1
    ): int {
        return $this->del(
            $tableUnsafe,
            $where,
            $filterUnsafe,
            $filterLogic,
            $limit
        );
    }
    
    /**
     * Method delRows
     *
     * @param string  $tableUnsafe  tableUnsafe
     * @param string  $where        where
     * @param mixed[] $filterUnsafe filterUnsafe
     * @param string  $filterLogic  filterLogic
     * @param int     $limit        limit
     *
     * @return int
     */
    public function delRows(
        string $tableUnsafe,
        string $where = '',
        array $filterUnsafe = [],
        string $filterLogic = 'AND',
        int $limit = 0
    ): int {
        return $this->delRow(
            $tableUnsafe,
            $where,
            $filterUnsafe,
            $filterLogic,
            $limit
        );
    }
    
    /**
     * Method del
     *
     * @param string  $tableUnsafe  tableUnsafe
     * @param string  $where        where
     * @param mixed[] $filterUnsafe filterUnsafe
     * @param string  $filterLogic  filterLogic
     * @param int     $limit        limit
     *
     * @return int
     * @throws RuntimeException
     */
    protected function del(
        string $tableUnsafe,
        string $where = '',
        array $filterUnsafe = [],
        string $filterLogic = 'AND',
        int $limit = 1
    ): int {
        if ($limit < 0) {
            throw new RuntimeException('Invalid limit: ' . $limit);
        }
        $table = $this->mysql->escape($tableUnsafe);
        $query = "DELETE FROM `$table`";
        $query .= $this->getWhere($table, $where, $filterUnsafe, $filterLogic);
        if ($limit >= 1) {
            $query .= " LIMIT $limit";
        }
        return $this->mysql->delete($query);
    }
    
    /**
     * Method setRow
     *
     * @param string  $tableUnsafe  tableUnsafe
     * @param mixed[] $valuesUnsafe valuesUnsafe
     * @param string  $where        where
     * @param mixed[] $filterUnsafe filterUnsafe
     * @param string  $filterLogic  filterLogic
     * @param int     $limit        limit
     *
     * @return int
     */
    public function setRow(
        string $tableUnsafe,
        array $valuesUnsafe,
        string $where = '',
        array $filterUnsafe = [],
        string $filterLogic = 'AND',
        int $limit = 1
    ): int {
        return $this->set(
            $tableUnsafe,
            $valuesUnsafe,
            $where,
            $filterUnsafe,
            $filterLogic,
            $limit
        );
    }
    
    /**
     * Method set
     *
     * @param string  $tableUnsafe  tableUnsafe
     * @param mixed[] $valuesUnsafe valuesUnsafe
     * @param string  $where        where
     * @param mixed[] $filterUnsafe filterUnsafe
     * @param string  $filterLogic  filterLogic
     * @param int     $limit        limit
     *
     * @return int
     */
    protected function set(
        string $tableUnsafe,
        array $valuesUnsafe,
        string $where = '',
        array $filterUnsafe = [],
        string $filterLogic = 'AND',
        int $limit = 1
    ): int {
        $table = $this->mysql->escape($tableUnsafe);
        $fields = $this->safer->freez([$this->mysql, 'escape'], $valuesUnsafe);
        $sets = [];
        foreach ($fields as $key => $value) {
            $sets[] = "`$table`.`$key` = " . $this->mysql->value($value);
        }
        $setstr = implode(', ', $sets);
        $where = $this->getWhere($table, $where, $filterUnsafe, $filterLogic);
        $query = "UPDATE `$table` SET $setstr $where";
        if ($limit >= 1) {
            $query .= " LIMIT $limit";
        }
        return $this->mysql->update($query);
    }
}
