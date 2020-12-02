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
use Throwable;

/**
 * Throwier
 *
 * @category  PHP
 * @package   Madsoft\Library
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */
class Throwier
{
    /**
     * Method throwPrevious
     *
     * @param Throwable $previous previous
     * @param string    $prefix   prefix
     * @param int|null  $code     code
     *
     * @return void
     * @throws RuntimeException
     */
    public function throwPrevious(
        Throwable $previous,
        string $prefix = 'An error occured: ',
        ?int $code = null
    ): void {
        if (null === $code) {
            $code = (int)$previous->getCode();
        }
        throw new RuntimeException(
            $prefix
                . $previous->getMessage()
                . ' ( ' . $previous->getCode() . ')',
            $code,
            $previous
        );
    }
}
