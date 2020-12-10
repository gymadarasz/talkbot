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

use Madsoft\Library\ErrorHandler;
use Madsoft\Library\Invoker;
use Madsoft\Library\Logger;

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
abstract class App extends ErrorHandler
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
        parent::__construct($invoker->getInstance(Logger::class));
        
        $this->invoker = $invoker;
    }
    
    /**
     * Method run
     *
     * @return App
     */
    abstract public function run(): App;
}
