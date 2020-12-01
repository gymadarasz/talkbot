<?php declare(strict_types = 1);

/**
 * PHP version 7.4
 *
 * @category  PHP
 * @package   Madsoft\Library\App
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */

namespace Madsoft\Library\App;

use Madsoft\Library\Invoker;
use RuntimeException;

/**
 * CliApp
 *
 * @category  PHP
 * @package   Madsoft\Library\App
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */
abstract class CliApp extends App
{
    
    /**
     * Method __construct
     *
     * @param Invoker $invoker invoker
     *
     * @throws RuntimeException
     */
    public function __construct(Invoker $invoker)
    {
        if (php_sapi_name() !== 'cli') {
            throw new RuntimeException('Cli App can run only from command line.');
        }
        parent::__construct($invoker);
    }

    /**
     * Method setArgv
     *
     * @param string[] $argv argv
     *
     * @return self
     */
    public function setArgv(array $argv): self
    {
        $this->argv = $argv;

        return $this;
    }
    
    /**
     * Method getExitResult
     *
     * @return int
     */
    public function getExitResult(): int
    {
        return $this->exitResult;
    }
}
