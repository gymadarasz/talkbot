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
    
    protected int $lastAddedOwnershipId = -1;
    
    protected Safer $safer;
    protected Mysql $mysql;
    protected User $user;
    protected Throwier $throwier;

    /**
     * Method __construct
     *
     * @param Safer    $safer    safer
     * @param Mysql    $mysql    mysql
     * @param User     $user     user
     * @param Throwier $throwier throwier
     */
    public function __construct(
        Safer $safer,
        Mysql $mysql,
        User $user,
        Throwier $throwier
    ) {
        $this->safer = $safer;
        $this->mysql = $mysql;
        $this->user = $user;
        $this->throwier = $throwier;
    }
    
    /**
     * Method getLastOwnershipInsertId
     *
     * @return int
     */
    public function getLastAddedOwnershipId(): int
    {
        $ret = $this->lastAddedOwnershipId;
        $this->lastAddedOwnershipId = -1;
        return $ret;
    }
    
    /**
     * Method getRow
     *
     * @param string   $tableUnsafe  tableUnsafe
     * @param string[] $fieldsUnsafe fieldsUnsafe
     * @param string[] $filterUnsafe filterUnsafe
     * @param string   $filterLogic  filterLogic
     *
     * @return string[]
     */
    public function getRow(
        string $tableUnsafe,
        array $fieldsUnsafe,
        array $filterUnsafe = [],
        string $filterLogic = 'AND'
    ): array {
        return $this->get(
            $tableUnsafe,
            $fieldsUnsafe,
            $filterUnsafe,
            $filterLogic,
            1,
            0,
            -1
        );
    }
    
    /**
     * Method getOwnedRow
     *
     * @param string   $tableUnsafe  tableUnsafe
     * @param string[] $fieldsUnsafe fieldsUnsafe
     * @param string[] $filterUnsafe filterUnsafe
     * @param string   $filterLogic  filterLogic
     * @param int      $uid          uid
     *
     * @return string[]
     */
    public function getOwnedRow(
        string $tableUnsafe,
        array $fieldsUnsafe,
        array $filterUnsafe = [],
        string $filterLogic = 'AND',
        int $uid = 0
    ): array {
        return $this->get(
            $tableUnsafe,
            $fieldsUnsafe,
            $filterUnsafe,
            $filterLogic,
            1,
            0,
            $uid
        );
    }
    
    /**
     * Method getRows
     *
     * @param string   $tableUnsafe  tableUnsafe
     * @param string[] $fieldsUnsafe fieldsUnsafe
     * @param string[] $filterUnsafe filterUnsafe
     * @param string   $filterLogic  filterLogic
     * @param int      $limit        limit
     * @param int      $offset       offset
     *
     * @return string[][]
     */
    public function getRows(
        string $tableUnsafe,
        array $fieldsUnsafe,
        array $filterUnsafe = [],
        string $filterLogic = 'AND',
        int $limit = 0,
        int $offset = 0
    ): array {
        return $this->get(
            $tableUnsafe,
            $fieldsUnsafe,
            $filterUnsafe,
            $filterLogic,
            $limit,
            $offset,
            -1
        );
    }
    
    /**
     * Method getOwnedRows
     *
     * @param string   $tableUnsafe  tableUnsafe
     * @param string[] $fieldsUnsafe fieldsUnsafe
     * @param string[] $filterUnsafe filterUnsafe
     * @param string   $filterLogic  filterLogic
     * @param int      $limit        limit
     * @param int      $offset       offset
     * @param int      $uid          uid
     *
     * @return string[][]
     */
    public function getOwnedRows(
        string $tableUnsafe,
        array $fieldsUnsafe,
        array $filterUnsafe = [],
        string $filterLogic = 'AND',
        int $limit = 0,
        int $offset = 0,
        int $uid = 0
    ): array {
        return $this->get(
            $tableUnsafe,
            $fieldsUnsafe,
            $filterUnsafe,
            $filterLogic,
            $limit,
            $offset,
            $uid
        );
    }
    
    /**
     * Method row
     *
     * @param string   $tableUnsafe  tableUnsafe
     * @param string[] $fieldsUnsafe fieldsUnsafe
     * @param string[] $filterUnsafe filterUnsafe
     * @param string   $filterLogic  filterLogic
     * @param int      $limit        limit
     * @param int      $offset       offset
     * @param int      $uid          uid
     *
     * @return mixed[]
     */
    protected function get(
        string $tableUnsafe,
        array $fieldsUnsafe,
        array $filterUnsafe = [],
        string $filterLogic = 'AND',
        int $limit = 1,
        int $offset = 0,
        int $uid = 0
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
        $query = "SELECT $fields FROM `$table`";
        
        if ($uid > -1) {
            if (!$uid) {
                $uid = $this->user->getId();
            }
            $query .= $this->getOwnerJoin($table, $uid);
        }
        
        $query .= $this->getWhere($table, $filterUnsafe, $filterLogic);
        if ($limit >= 1) {
            $query .= " LIMIT $offset, $limit";
        }
        if ($limit === 1) {
            return $this->mysql->selectOne($query);
        }
        return $this->mysql->select($query);
    }
    
    /**
     * Method getOwnerJoin
     *
     * @param string $table table
     * @param int    $uid   uid
     *
     * @return string
     */
    protected function getOwnerJoin(string $table, int $uid): string
    {
        return " JOIN `ownership` "
            . "ON `ownership`.`row_id` = `$table`.`id` "
            . "AND `ownership`.`table` = '$table' "
            . "AND `ownership`.`user_id` = $uid";
    }
    
    /**
     * Method getWhere
     *
     * @param string   $table        table
     * @param string[] $filterUnsafe filterUnsafe
     * @param string   $logic        logic
     *
     * @return string
     * @throws RuntimeException
     */
    protected function getWhere(
        string $table,
        array $filterUnsafe,
        string $logic
    ): string {
        $filter = $this->safer->freez([$this->mysql, 'escape'], $filterUnsafe);
        if (!in_array($logic, self::LOGICS, true)) {
            throw new RuntimeException("Invalid logic: '$logic'");
        }
        $query = '';
        if ($filter) {
            $query .= " WHERE " . $this->getConditions($table, $filter, $logic);
        }
        return $query;
    }
    
    /**
     * Method getConditions
     *
     * @param string   $table  table
     * @param string[] $filter filter
     * @param string   $logic  logic
     *
     * @return string
     */
    protected function getConditions(
        string $table,
        array $filter,
        string $logic
    ): string {
        $conds = [];
        foreach ($filter as $key => $value) {
            $conds[] = "`$table`.`$key` = '$value'";
        }
        return $conds ?
            implode(" $logic ", $conds) :
            $this::NO_CONDITION_FILTERS[$logic];
    }
    
    /**
     * Method addRow
     *
     * @param string   $tableUnsafe  tableUnsafe
     * @param string[] $valuesUnsafe valuesUnsafe
     *
     * @return int
     */
    public function addRow(string $tableUnsafe, array $valuesUnsafe): int
    {
        return $this->add($tableUnsafe, $valuesUnsafe, -1);
    }
    
    /**
     * Method addOwnedRow
     *
     * @param string   $tableUnsafe  tableUnsafe
     * @param string[] $valuesUnsafe valuesUnsafe
     *
     * @return int
     */
    public function addOwnedRow(string $tableUnsafe, array $valuesUnsafe): int
    {
        return $this->add($tableUnsafe, $valuesUnsafe, 0);
    }
    
    /**
     * Method add
     *
     * @param string   $tableUnsafe  tableUnsafe
     * @param string[] $valuesUnsafe valuesUnsafe
     * @param int      $uid          uid
     *
     * @return int
     */
    protected function add(
        string $tableUnsafe,
        array $valuesUnsafe,
        int $uid = 0
    ): int {
        $this->mysql->getTransaction()->start();
        try {
            $rid = $this->addInsert($tableUnsafe, $valuesUnsafe);

            if ($uid > -1) {
                if (!$uid) {
                    $uid = $this->user->getId();
                }
                $this->lastAddedOwnershipId = $this->addInsert(
                    'ownership',
                    [
                        'table' => $tableUnsafe,
                        'row_id' => (string)$rid,
                        'user_id' => (string)$uid,
                    ]
                );
            }
        } catch (RuntimeException $exception) {
            $this->mysql->getTransaction()->rollback();
            throw $this->throwier->forward($exception);
        }
        $this->mysql->getTransaction()->commit();
        
        return $rid;
    }
    
    /**
     * Method addInsert
     *
     * @param string   $tableUnsafe  tableUnsafe
     * @param string[] $valuesUnsafe valuesUnsafe
     *
     * @return int
     */
    protected function addInsert(string $tableUnsafe, array $valuesUnsafe): int
    {
        $table = $this->mysql->escape($tableUnsafe);
        $fields = $this->safer->freez([$this->mysql, 'escape'], $valuesUnsafe);
        $keys = implode('`, `', array_keys($fields));
        $values = implode("', '", $fields);
        $query = "INSERT INTO `$table` (`$keys`) VALUES ('$values')";
        return $this->mysql->insert($query);
    }
    
    /**
     * Method delRow
     *
     * @param string   $tableUnsafe  tableUnsafe
     * @param string[] $filterUnsafe filterUnsafe
     * @param string   $filterLogic  filterLogic
     * @param int      $limit        limit
     * @param int      $offset       offset
     *
     * @return int
     */
    public function delRow(
        string $tableUnsafe,
        array $filterUnsafe,
        string $filterLogic = 'AND',
        int $limit = 1,
        int $offset = 0
    ): int {
        return $this->del(
            $tableUnsafe,
            $filterUnsafe,
            $filterLogic,
            $limit,
            $offset,
            -1
        );
    }
    
    /**
     * Method delRows
     *
     * @param string   $tableUnsafe  tableUnsafe
     * @param string[] $filterUnsafe filterUnsafe
     * @param string   $filterLogic  filterLogic
     * @param int      $limit        limit
     * @param int      $offset       offset
     *
     * @return int
     */
    public function delRows(
        string $tableUnsafe,
        array $filterUnsafe,
        string $filterLogic = 'AND',
        int $limit = 0,
        int $offset = 0
    ): int {
        return $this->delRow(
            $tableUnsafe,
            $filterUnsafe,
            $filterLogic,
            $limit,
            $offset
        );
    }
    
    /**
     * Method delOwnedRow
     *
     * @param string   $tableUnsafe  tableUnsafe
     * @param string[] $filterUnsafe filterUnsafe
     * @param string   $filterLogic  filterLogic
     * @param int      $limit        limit
     * @param int      $offset       offset
     *
     * @return int
     */
    public function delOwnedRow(
        string $tableUnsafe,
        array $filterUnsafe,
        string $filterLogic = 'AND',
        int $limit = 1,
        int $offset = 0
    ): int {
        return $this->del(
            $tableUnsafe,
            $filterUnsafe,
            $filterLogic,
            $limit,
            $offset,
            0
        );
    }
    
    /**
     * Method delOwnedRows
     *
     * @param string   $tableUnsafe  tableUnsafe
     * @param string[] $filterUnsafe filterUnsafe
     * @param string   $filterLogic  filterLogic
     * @param int      $limit        limit
     * @param int      $offset       offset
     *
     * @return int
     */
    public function delOwnedRows(
        string $tableUnsafe,
        array $filterUnsafe,
        string $filterLogic = 'AND',
        int $limit = 0,
        int $offset = 0
    ): int {
        return $this->delOwnedRow(
            $tableUnsafe,
            $filterUnsafe,
            $filterLogic,
            $limit,
            $offset
        );
    }
    
    /**
     * Method del
     *
     * @param string   $tableUnsafe  tableUnsafe
     * @param string[] $filterUnsafe filterUnsafe
     * @param string   $filterLogic  filterLogic
     * @param int      $limit        limit
     * @param int      $offset       offset
     * @param int      $uid          uid
     *
     * @return int
     * @throws RuntimeException
     */
    protected function del(
        string $tableUnsafe,
        array $filterUnsafe,
        string $filterLogic = 'AND',
        int $limit = 1,
        int $offset = 0,
        int $uid = 0
    ): int {
        if ($limit < 0) {
            throw new RuntimeException('Invalid limit: ' . $limit);
        }
        $this->validateOwner(
            $tableUnsafe,
            $filterUnsafe,
            $filterLogic,
            $limit,
            $uid
        );
        $table = $this->mysql->escape($tableUnsafe);
        $query = "DELETE FROM `$table`";
        $query .= $this->getWhere($table, $filterUnsafe, $filterLogic);
        if ($limit >= 1) {
            $query .= " LIMIT $offset, $limit";
        }
        return $this->mysql->delete($query);
    }
    
    /**
     * Method validateOwner
     *
     * @param string   $tableUnsafe  tableUnsafe
     * @param string[] $filterUnsafe filterUnsafe
     * @param string   $filterLogic  filterLogic
     * @param int      $limit        limit
     * @param int      $uid          uid
     *
     * @return void
     * @throws RuntimeException
     */
    protected function validateOwner(
        string $tableUnsafe,
        array $filterUnsafe,
        string $filterLogic,
        int $limit,
        int $uid
    ): void {
        if ($uid > -1) {
            if ($limit === 1) {
                $this->getOwnedRow(
                    $tableUnsafe,
                    ['id'],
                    $filterUnsafe,
                    $filterLogic,
                    $uid
                );
                return;
            }
            $this->getOwnedRows(
                $tableUnsafe,
                ['id'],
                $filterUnsafe,
                $filterLogic,
                $limit,
                $uid
            );
            return;
        }
    }
    
    /**
     * Method setRow
     *
     * @param string   $tableUnsafe  tableUnsafe
     * @param string[] $valuesUnsafe valuesUnsafe
     * @param string[] $filterUnsafe filterUnsafe
     * @param string   $filterLogic  filterLogic
     * @param int      $limit        limit
     *
     * @return int
     */
    public function setRow(
        string $tableUnsafe,
        array $valuesUnsafe,
        array $filterUnsafe,
        string $filterLogic = 'AND',
        int $limit = 1
    ): int {
        return $this->set(
            $tableUnsafe,
            $valuesUnsafe,
            $filterUnsafe,
            $filterLogic,
            $limit,
            -1
        );
    }
    
    /**
     * Method set
     *
     * @param string   $tableUnsafe  tableUnsafe
     * @param string[] $valuesUnsafe valuesUnsafe
     * @param string[] $filterUnsafe filterUnsafe
     * @param string   $filterLogic  filterLogic
     * @param int      $limit        limit
     * @param int      $uid          uid
     *
     * @return int
     */
    public function setOwnedRow(
        string $tableUnsafe,
        array $valuesUnsafe,
        array $filterUnsafe,
        string $filterLogic = 'AND',
        int $limit = 1,
        int $uid = 0
    ): int {
        return $this->set(
            $tableUnsafe,
            $valuesUnsafe,
            $filterUnsafe,
            $filterLogic,
            $limit,
            $uid
        );
    }
    
    /**
     * Method set
     *
     * @param string   $tableUnsafe  tableUnsafe
     * @param string[] $valuesUnsafe valuesUnsafe
     * @param string[] $filterUnsafe filterUnsafe
     * @param string   $filterLogic  filterLogic
     * @param int      $limit        limit
     * @param int      $uid          uid
     *
     * @return int
     */
    protected function set(
        string $tableUnsafe,
        array $valuesUnsafe,
        array $filterUnsafe,
        string $filterLogic = 'AND',
        int $limit = 1,
        int $uid = 0
    ): int {
        $this->validateOwner(
            $tableUnsafe,
            $filterUnsafe,
            $filterLogic,
            1,
            $uid
        );
        $table = $this->mysql->escape($tableUnsafe);
        $fields = $this->safer->freez([$this->mysql, 'escape'], $valuesUnsafe);
        $sets = [];
        foreach ($fields as $key => $value) {
            $sets[] = "`$table`.`$key` = '$value'";
        }
        $setstr = implode(', ', $sets);
        $where = $this->getWhere($table, $filterUnsafe, $filterLogic);
        $query = "UPDATE `$table` SET $setstr $where";
        if ($limit >= 1) {
            $query .= " LIMIT $limit";
        }
        return $this->mysql->update($query);
    }
}
