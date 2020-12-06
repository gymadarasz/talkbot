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

/**
 * App
 *
 * @category  PHP
 * @package   Madsoft\Library\App
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */
abstract class App
{
    /**
     * Variable $argv
     *
     * @var string[]
     */
    protected array $argv;
    protected int $exitResult;
    
    protected Invoker $invoker;
    
    /**
     * Method __construct
     *
     * @param Invoker $invoker invoker
     */
    public function __construct(Invoker $invoker)
    {
        ini_set('display_errors', '1');
        ini_set('display_startup_errors', '1');
        error_reporting(E_ALL | E_STRICT);
        $this->invoker = $invoker;
    }
    
    /**
     * Method run
     *
     * @return App
     */
    abstract public function run(): App;
}
