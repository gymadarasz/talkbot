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
     * @param string   $join         join
     * @param string   $where        where
     * @param mixed[]  $filterUnsafe filterUnsafe
     * @param string   $filterLogic  filterLogic
     *
     * @return string[]
     */
    public function getRow(
        string $tableUnsafe,
        array $fieldsUnsafe,
        string $join = '',
        string $where = '',
        array $filterUnsafe = [],
        string $filterLogic = 'AND'
    ): array {
        $count = $this->count(
            $tableUnsafe,
            $join,
            $where,
            $filterUnsafe,
            $filterLogic
        );
        if ($count === 0) {
            return [];
        }
        if ($count === 1) {
            return $this->get(
                $tableUnsafe,
                $fieldsUnsafe,
                $join,
                $where,
                $filterUnsafe,
                $filterLogic,
                1,
                0
            );
        }
        throw new RuntimeException(
            'Getter query expect only and exactly one row but found ' . $count
        );
    }
    
    /**
     * Method getRows
     *
     * @param string   $tableUnsafe  tableUnsafe
     * @param string[] $fieldsUnsafe fieldsUnsafe
     * @param string   $join         join
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
        string $join = '',
        string $where = '',
        array $filterUnsafe = [],
        string $filterLogic = 'AND',
        int $limit = 0,
        int $offset = 0
    ): array {
        if (!$this->count(
            $tableUnsafe,
            $join,
            $where,
            $filterUnsafe,
            $filterLogic
        )
        ) {
            return [];
        }
        return $this->get(
            $tableUnsafe,
            $fieldsUnsafe,
            $join,
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
     * @param string   $join         join
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
        string $join = '',
        string $where = '',
        array $filterUnsafe = [],
        string $filterLogic = 'AND',
        int $limit = 1,
        int $offset = 0
    ): array {
        $query = $this->getSelect(
            $tableUnsafe,
            $fieldsUnsafe,
            $join,
            $where,
            $filterUnsafe,
            $filterLogic
        );
        if ($limit >= 1) {
            $query .= " LIMIT $offset, $limit";
        }
        if ($limit === 1) {
            return $this->mysql->selectOne($query);
        }
        return $this->mysql->select($query);
    }
    
    /**
     * Method count
     *
     * @param string  $tableUnsafe  tableUnsafe
     * @param string  $join         join
     * @param string  $where        where
     * @param mixed[] $filterUnsafe filterUnsafe
     * @param string  $filterLogic  filterLogic
     *
     * @return int
     */
    public function count(
        string $tableUnsafe,
        string $join = '',
        string $where = '',
        array $filterUnsafe = [],
        string $filterLogic = 'AND'
    ): int {
        $this->mysql->setEscapeSql(true);
        $table = $this->mysql->escape($tableUnsafe);
        $query = $this->getSelectQuery(
            'COUNT(*) as count_results_of_select',
            $table,
            $join,
            $where,
            $filterUnsafe,
            $filterLogic
        );
        $this->mysql->setEscapeSql(false);
        return (int)$this->mysql->selectOne($query)['count_results_of_select'];
    }
    
    /**
     * Method getSelect
     *
     * @param string   $tableUnsafe  tableUnsafe
     * @param string[] $fieldsUnsafe fieldsUnsafe
     * @param string   $join         join
     * @param string   $where        where
     * @param mixed[]  $filterUnsafe filterUnsafe
     * @param string   $filterLogic  filterLogic
     *
     * @return string
     */
    protected function getSelect(
        string $tableUnsafe,
        array $fieldsUnsafe,
        string $join = '',
        string $where = '',
        array $filterUnsafe = [],
        string $filterLogic = 'AND'
    ): string {
        $this->mysql->setEscapeSql(true);
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
        $this->mysql->setEscapeSql(false);
        return $this->getSelectQuery(
            $fields,
            $table,
            $join,
            $where,
            $filterUnsafe,
            $filterLogic
        );
    }
    
    /**
     * Method getSelectQuery
     *
     * @param string  $fields       fields
     * @param string  $table        table
     * @param string  $join         join
     * @param string  $where        where
     * @param mixed[] $filterUnsafe filterUnsafe
     * @param string  $filterLogic  filterLogic
     *
     * @return string
     */
    protected function getSelectQuery(
        string $fields,
        string $table,
        string $join = '',
        string $where = '',
        array $filterUnsafe = [],
        string $filterLogic = 'AND'
    ): string {
        return "SELECT $fields FROM `$table`"
            . ($join ? " $join": '')
            . $this->getWhere($table, $where, $filterUnsafe, $filterLogic);
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
        $this->mysql->setEscapeSql(true);
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
        $this->mysql->setEscapeSql(false);
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
        $this->mysql->setEscapeSql(true);
        $conds = [];
        foreach ($filter as $key => $value) {
            if (is_int($key)) {
                throw new RuntimeException(
                    'Invalid filter array. Filter should be an associative array: '
                        . 'filter[field] => value'
                );
            }
            $splits = explode('.', $key);
            if (count($splits) === 1) {
                $conds[] = "`$table`.`$key` = "
                    . $this->mysql->value($value);
                continue;
            }
            if (count($splits) === 2) {
                $conds[] = "`{$splits[0]}`.`{$splits[1]}` = "
                    . $this->mysql->value($value);
                continue;
            }
            throw new RuntimeException(
                'Invalid filter key given: "' . $value . '", '
                    . 'filter keys should describe a [table.]field name.'
            );
        }
        
        $ret = $conds ?
            implode(" $logic ", $conds) :
            $this::NO_CONDITION_FILTERS[$logic];
        if (trim($where)) {
            $ret = "($ret) $where";
        }
        $this->mysql->setEscapeSql(false);
        return $ret;
    }
    
    /**
     * Method addRow
     *
     * @param string  $tableUnsafe  tableUnsafe
     * @param mixed[] $valuesUnsafe valuesUnsafe
     *
     * @return int|string
     */
    public function addRow(
        string $tableUnsafe,
        array $valuesUnsafe
    ) {
        return $this->add($tableUnsafe, $valuesUnsafe);
    }
    
    /**
     * Method add
     *
     * @param string  $tableUnsafe  tableUnsafe
     * @param mixed[] $valuesUnsafe valuesUnsafe
     *
     * @return int|string
     */
    protected function add(
        string $tableUnsafe,
        array $valuesUnsafe
    ) {
        $this->mysql->setEscapeSql(true);
        $table = $this->mysql->escape($tableUnsafe);
        $fields = $this->safer->freez([$this->mysql, 'escape'], $valuesUnsafe);
        $keys = implode('`, `', array_keys($fields));
        foreach ($fields as $key => $field) {
            $fields[$key] = $this->mysql->value($field);
        }
        $values = implode(", ", $fields);
        $query = "INSERT INTO `$table` (`$keys`) VALUES ($values)";
        $this->mysql->setEscapeSql(false);
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
        $count = $this->count($tableUnsafe, '', $where, $filterUnsafe, $filterLogic);
        if (!$count) {
            return 0;
        }
        if ($count === 1) {
            return $this->del(
                $tableUnsafe,
                $where,
                $filterUnsafe,
                $filterLogic,
                $limit
            );
        }
        throw new RuntimeException(
            'Delete query expect only and exactly one row but found ' . $count
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
        if ($this->count($tableUnsafe, '', $where, $filterUnsafe, $filterLogic)) {
            return $this->del(
                $tableUnsafe,
                $where,
                $filterUnsafe,
                $filterLogic,
                $limit
            );
        }
        return 0;
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
        $this->mysql->setEscapeSql(true);
        if ($limit < 0) {
            throw new RuntimeException('Invalid limit: ' . $limit);
        }
        $table = $this->mysql->escape($tableUnsafe);
        $query = "DELETE FROM `$table`";
        $query .= $this->getWhere($table, $where, $filterUnsafe, $filterLogic);
        if ($limit >= 1) {
            $query .= " LIMIT $limit";
        }
        $this->mysql->setEscapeSql(false);
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
        if (!$this->count($tableUnsafe, '', $where, $filterUnsafe, $filterLogic)) {
            return 0;
        }
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
        $this->mysql->setEscapeSql(true);
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
        $this->mysql->setEscapeSql(false);
        return $this->mysql->update($query);
    }
}
