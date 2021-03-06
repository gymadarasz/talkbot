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

use Madsoft\Library\App\ApiApp;
use Madsoft\Library\App\App;
use Madsoft\Library\App\CliApp;
use Madsoft\Library\App\WebApp;
use Madsoft\Library\Tester\Tester;
use RuntimeException;

/**
 * TestApp
 *
 * @category  PHP
 * @package   Madsoft\Library
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 *
 * @suppress PhanUnreferencedClass
 */
class TestApp extends CliApp
{
    protected ApiApp $apiApp;
    protected WebApp $webApp;
    
    /**
     * Method __construct
     *
     * @param Invoker $invoker invoker
     * @param ApiApp  $apiApp  apiApp
     * @param WebApp  $webApp  webApp
     */
    public function __construct(Invoker $invoker, ApiApp $apiApp, WebApp $webApp)
    {
        parent::__construct($invoker);
        $this->apiApp = $apiApp;
        $this->webApp = $webApp;
    }
    
    /**
     * Method run
     *
     * @return App
     * @throws RuntimeException
     */
    public function run(): App
    {
        $tester = $this->invoker->getInstance(Tester::class);

        $tester->getCoverage()->start(
            [
                __DIR__ . "/../../vendor/",
                __DIR__ . "/../../src/Library/Coverage/Coverage.php",
                __DIR__ . "/../../src/Library/Tester/Test.php",
                __DIR__ . "/../../src/Library/Tester/Tester.php",
                __DIR__ . "/../../tests/",
                $this->apiApp->getRouteCacheFile(),
                $this->webApp->getRouteCacheFile(),
            ]
        );

        $argv = $this->argv;
        array_shift($argv);
        $runAll = false;
        if (empty($argv)) {
            $runAll = true;
        }
        if ($runAll) {
            $tester->test();
        }
        if (!$runAll) {
            foreach ($argv as $arg) {
                $tester->runTestFile($arg, __DIR__ . "/../../");
            }
        }

        $tester->cleanUp();

        $this->exitResult = $tester->stat() ? 0 : 1;
        
        return $this;
    }
}
