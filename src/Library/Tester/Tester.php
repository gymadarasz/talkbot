<?php declare(strict_types = 1);

/**
 * PHP version 7.4
 *
 * @category  PHP
 * @package   Madsoft\Library\Tester
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */

namespace Madsoft\Library\Tester;

use Exception;
use Madsoft\Library\Config;
use Madsoft\Library\Coverage\Coverage;
use Madsoft\Library\Folders;
use Madsoft\Library\Invoker;
use Madsoft\Library\Logger;
use ReflectionClass;
use RuntimeException;

/**
 * Tester
 *
 * @category  PHP
 * @package   Madsoft\Library\Tester
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */
class Tester extends Test
{
    const TESTS_PATHES = [
        __DIR__ . '/../../../tests',
    ];
    
    /**
     * Variable $cleaners
     *
     * @var string[]
     */
    protected array $cleaners = [];
    
    protected Folders $folders;
    protected Invoker $invoker;
    protected Coverage $coverage;

    /**
     * Method __construct
     *
     * @param Folders  $folders  folders
     * @param Invoker  $invoker  invoker
     * @param Coverage $coverage coverage
     * @param Config   $config   config
     *
     * @throws RuntimeException
     */
    public function __construct(
        Folders $folders,
        Invoker $invoker,
        Coverage $coverage,
        Config $config
    ) {
        $this->folders = $folders;
        $this->invoker = $invoker;
        $this->coverage = $coverage;
        
        $env = $config->getEnv();
        if ($env !== 'test') {
            throw new RuntimeException(
                'Environment should be "test", actual $env is "' . $env . '"'
            );
        }
    }
    
    /**
     * Method setCleaners
     *
     * @param string[] $cleaners cleaners
     *
     * @return self
     *
     * @suppress PhanUnreferencedPublicMethod
     */
    public function setCleaners(array $cleaners): self
    {
        $this->cleaners = $cleaners;
        return $this;
    }
    
    /**
     * Method cleanUp
     *
     * @return void
     *
     * @suppress PhanUnreferencedPublicMethod
     */
    public function cleanUp(): void
    {
        foreach ($this->cleaners as $cleaner) {
            $this->invoker->getInstance($cleaner)->cleanUp();
        }
    }
    
    /**
     * Method getCoverage
     *
     * @return Coverage
     *
     * @suppress PhanUnreferencedPublicMethod
     */
    public function getCoverage(): Coverage
    {
        return $this->coverage;
    }
    
    /**
     * Method test
     *
     * @param string[] $pathes pathes
     *
     * @return void
     */
    public function test(array $pathes = self::TESTS_PATHES): void
    {
        $this->setCleaners($this->findCleaners($pathes))->cleanUp();
        
        foreach ($pathes as $path) {
            $files = $this->folders->getFilesRecursive($path);
            foreach ($files as $file) {
                $this->runTestFile($file->getFilename(), $file->getPath());
            }
        }
    }
    
    /**
     * Method findCleaners
     *
     * @param string[] $pathes pathes
     *
     * @return string[]
     */
    protected function findCleaners(array $pathes = self::TESTS_PATHES): array
    {
        $fullclasses = [];
        foreach ($pathes as $path) {
            $files = $this->folders->getFilesRecursive($path);
            foreach ($files as $file) {
                $filename = $file->getFilename();
                $matches = [];
                if (preg_match(
                    '/([a-zA-Z0-9][a-zA-Z0-9_]*TestCleaner).php$/',
                    $filename,
                    $matches
                )
                ) {
                    $class = $matches[1];

                    $fullname = $file->getPath() . '/' . $filename;
                    $namespace = $this->getPhpNamespace($fullname);

                    $fullclasses[] = "$namespace\\$class";
                }
            }
        }
        return $fullclasses;
    }
    
    /**
     * Method runTestFile
     *
     * @param string $filename filename
     * @param string $path     path
     *
     * @return void
     */
    public function runTestFile(
        string $filename,
        string $path
    ): void {
        $matches = [];
        if (preg_match(
            '/([a-zA-Z0-9][a-zA-Z0-9_]*Test).php$/',
            $filename,
            $matches
        )
        ) {
            $class = $matches[1];

            $fullname = $path . '/' . $filename;
            $namespace = $this->getPhpNamespace($fullname);

            $fullclass = "$namespace\\$class";

            include_once $fullname;
            $this->run($fullclass);
        }
    }
    
    /**
     * Method getPhpNamespace
     *
     * @param string $fullname fullname
     *
     * @return string
     * @throws RuntimeException
     */
    protected function getPhpNamespace(string $fullname): string
    {
        $contents = file_get_contents($fullname);
        if (false === $contents) {
            throw new RuntimeException(
                'Unable to read test file: ' . $fullname
            );
        }
        $matches = [];
        if (preg_match('/namespace\s+(.+);/', $contents, $matches)) {
            return $matches[1];
        }
        return '';
    }
    
    /**
     * Method run
     *
     * @param string $class class
     *
     * @return void
     * @throws RuntimeException
     */
    protected function run(string $class): void
    {
        $methods = $this->getClassExactMethods($class);
        $test = $this->getTest($class);
        if (method_exists($test, 'beforeAll')) {
            $this->invoker->invoke(['class' => $class, 'method' => 'beforeAll']);
        }
        foreach ($methods as $method) {
            if (preg_match('/^test/', $method)) {
                try {
                    if (method_exists($test, 'before')) {
                        $this->invoker->invoke(
                            ['class' => $class, 'method' => 'before']
                        );
                    }
                    $this->invoker->invoke(['class' => $class, 'method' => $method]);
                    if (method_exists($test, 'after')) {
                        $this->invoker->invoke(
                            ['class' => $class, 'method' => 'after']
                        );
                    }
                } catch (Exception $exception) {
                    $test->assertFalse(
                        true,
                        "Tests should not throws exception but it's happened at "
                            . "$class::$method(), exception details:\n"
                            . (new Logger())->exceptionToString($exception)
                    );
                }
                if (!$test->asserts) {
                    throw new RuntimeException(
                        "$class::$method() has not any assertation."
                    );
                }
                $this->failInfos = array_merge($test->failInfos, $this->failInfos);
                $this->asserts += $test->asserts;
                $this->success += $test->success;
                $this->fails += $test->fails;
            }
        }
        if (method_exists($test, 'afterAll')) {
            $this->invoker->invoke(['class' => $class, 'method' => 'afterAll']);
        }
        $this->invoker->free($class);
    }
    
    /**
     * Method getClassExactMethods
     *
     * @param mixed $class class
     *
     * @return string[]
     */
    protected function getClassExactMethods($class): array
    {
        $ref = new ReflectionClass($class);
        $methods = array();
        foreach ($ref->getMethods() as $method) {
            if ($method->class == $class) {
                $methods[] = $method->name;
            }
        }
        return $methods;
    }
    
    /**
     * Method getTest
     *
     * @param string $class class
     *
     * @return Test
     */
    protected function getTest(string $class): Test
    {
        return $this->invoker->getInstance($class);
    }
    
    /**
     * Method stat
     *
     * @param float  $coverageThreshold coverageThreshold
     * @param string $coverageOutput    coverageOutput
     *
     * @return bool
     *
     * @suppress PhanUnreferencedPublicMethod
     */
    public function stat(
        float $coverageThreshold = 100.0,
        string $coverageOutput = __DIR__ . '/coverage/coverage.html'
    ): bool {
        $coverageData = [];
        $coveragePassed = true;
        if ($this->coverage->isStarted()) {
            $coverageData = $this->coverage->getCoverageData();
            $coveragePercentage = $this->coverage->getPercentage($coverageData);
            $coveragePassed = $coverageThreshold <= $coveragePercentage;
            echo "\nCode coverage:\t$coveragePercentage %";
        }
        echo "\nAsserts:\t{$this->asserts}";
        echo "\nSuccess:\t{$this->success}";
        if ($this->fails) {
            echo "\nFails:\t{$this->fails}";
            echo "\n\n" . implode("\n\n", $this->failInfos);
            echo "\n";
            return false;
        }
        if (!$coveragePassed) {
            echo "\nCoverage thrashold ($coverageThreshold %) failed.";
            echo "\nGenerate coverage info to '$coverageOutput'...";
            $output = $this->coverage->generateCoverageInfo($coverageData);
            $writeOk = false === file_put_contents($coverageOutput, $output);
            if ($writeOk) {
                echo '[ERR]';
            }
            if (!$writeOk) {
                echo "[OK]";
            }
            echo "\n";
            return false;
        }
        echo "\n";
        return true;
    }
}
