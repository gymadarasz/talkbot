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

/**
 * Merger
 *
 * @category  PHP
 * @package   Madsoft\Library
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */
class Merger
{
    /**
     * Method merge
     *
     * @param mixed[] $arr1  arr1
     * @param mixed[] $arr2  arr2
     * @param string  $joker joker
     *
     * @return mixed[]
     *
     * @suppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function merge(
        array $arr1,
        array $arr2,
        string $joker = '*'
    ): array {
        $merg = $arr1;

        foreach ($arr2 as $key => $value) {
            if ($key === $joker) {
                foreach ($merg as $mkey => $mval) {
                    if (is_numeric($mkey)) {
                        $merg[] = $value;
                        break;
                    }
                    if (is_array($mval) && is_array($value)) {
                        $merg[$mkey] = $this->merge($mval, $value, $joker);
                        continue;
                    }
                    $merg[$mkey] = $value;
                }
                continue;
            }

            if (is_numeric($key) && !in_array($value, $merg, true)) {
                $merg[] = $value;
                continue;
            }

            if (isset($merg[$key]) && is_array($merg[$key]) && is_array($value)) {
                $merg[$key] = $this->merge($merg[$key], $value, $joker);
                continue;
            }

            if (is_string($key)
                && (!isset($merg[$key])
                || !(is_array($merg[$key]) && is_array($value)))
            ) {
                $merg[$key] = $value;
                continue;
            }
        }

        return $merg;
    }
}
