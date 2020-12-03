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
     * Method forward
     *
     * @param Throwable $exception exception
     * @param string    $prefix    prefix
     * @param int|null  $code      code
     *
     * @return RuntimeException
     */
    public function forward(
        Throwable $exception,
        string $prefix = 'An error occured: ',
        ?int $code = null
    ): RuntimeException {
        if (null === $code) {
            $code = (int)$exception->getCode();
        }
        return new RuntimeException(
            $prefix
                . $exception->getMessage()
                . ' ( ' . $exception->getCode() . ')',
            $code,
            $exception
        );
    }
}
