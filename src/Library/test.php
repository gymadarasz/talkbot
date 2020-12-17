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

require_once __DIR__ . '/../../vendor/autoload.php';

return (new Invoker)
    ->getInstance(TestApp::class)
    ->setArgv($argv)
    ->run()
    ->getExitResult();
