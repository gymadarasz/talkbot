<?php declare(strict_types = 1);

/**
 * PHP version 7.4
 *
 * @category  PHP
 * @package   Madsoft\Library\Layout\View
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */

namespace Madsoft\Library\Layout\View;

/**
 * RowView
 *
 * @category  PHP
 * @package   Madsoft\Library\Layout\View
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */
class RowView
{
    /**
     * Method getDatasetParams
     *
     * @param string[] $dataset dataset
     *
     * @return mixed[]
     */
    public function getDatasetParams(array $dataset): array
    {
        return [
            $dataset['table'],
            $dataset['fields'],
            $dataset['join'],
            $dataset['where'],
            $dataset['filter'],
            $dataset['filterLogic']
        ];
    }
}
