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

use Madsoft\Library\Mysql;
use Madsoft\Library\Tester\Test;

/**
 * MysqlTest
 *
 * @category  PHP
 * @package   Madsoft\Tests\Library
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */
class MysqlTest extends Test
{
    /**
     * Method testMysql
     *
     * @param Mysql $mysql mysql
     *
     * @return void
     *
     * @suppress PhanUnreferencedPublicMethod
     */
    public function testMysql(Mysql $mysql): void
    {
        $mysql->delete("DELETE FROM user WHERE hash = 'test'");
        $mysql->insert(
            "INSERT INTO user (email, hash, token) VALUES ('test1', 'test', '1')"
        );
        $mysql->insert(
            "INSERT INTO user (email, hash, token) VALUES ('test2', 'test', '2')"
        );
        $mysql->insert(
            "INSERT INTO user (email, hash, token) VALUES ('test3', 'test', '3')"
        );
        $results = $mysql->select(
            "SELECT email FROM user WHERE hash = 'test' ORDER BY token"
        );
        $this->assertEquals(3, count($results));
        $this->assertEquals('test1', $results[0]['email']);
        $this->assertEquals('test2', $results[1]['email']);
        $this->assertEquals('test3', $results[2]['email']);
    }
}
