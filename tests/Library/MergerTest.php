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

use Madsoft\Library\Merger;
use Madsoft\Library\Tester\Test;

/**
 * MergerTest
 *
 * @category  PHP
 * @package   Madsoft\Tests\Library
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */
class MergerTest extends Test
{
    /**
     * Method testMerge
     *
     * @param Merger $merger merger
     *
     * @return void
     *
     * @suppress PhanUnreferencedPublicMethod
     */
    public function testMerge(Merger $merger): void
    {
        $results = $merger->merge([[['a']]], [[['a']]]);
        $this->assertEquals([[['a']]], $results);
        $results = $merger->merge([[['a', 'b']]], [[['b', 'c']]]);
        $this->assertEquals([[['a', 'b', 'c']]], $results);
        $results = $merger->merge([[['a', 'b']]], [[['b', ['c']]]]);
        $this->assertEquals([[['a', 'b', ['c']]]], $results);
    }
    
    /**
     * Method testMergeJoker
     *
     * @param Merger $merger merger
     *
     * @return void
     *
     * @suppress PhanUnreferencedPublicMethod
     */
    public function testMergeJoker(Merger $merger): void
    {
        $arr1 = [
            'top' =>
            [
                'foo' => [
                    'bar' => [
                        'value1', 'value2'
                    ],
                ],
                'foo2' => [
                    'ize' => 'mize',
                    'bar' => 'changing'
                ],
                'foo3' => [
                    'bar' => 'overwriten',
                ],
            ]
        ];
        $arr2 = [
            '*' => [
                '*' => [
                    'bar' => [
                        'bazz'
                    ],
                ],
            ],
        ];
        $results = $merger->merge($arr1, $arr2);
        $this->assertEquals(
            [
            'top' => [
                'foo' => [
                    'bar' => [
                        'value1', 'value2', 'bazz'
                    ],
                ],
                'foo2' => [
                    'ize' => 'mize',
                    'bar' => [
                        'bazz'
                    ],
                ],
                'foo3' => [
                    'bar' => [
                        'bazz'
                    ],
                ],
            ],
            ],
            $results
        );
    }
}
