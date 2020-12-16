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
use SplFileInfo;

/**
 * RouteCache
 *
 * @category  PHP
 * @package   Madsoft\Library
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */
class RouteCache
{
    const ROUTE_CACHE_FILEPATH = __DIR__ . '/../../../';
    const ROUTE_CACHE_FILENAME = 'routes.cache.php';
    const SOURCE_FOLDER = __DIR__ . '/../';
    
    protected Config $config;
    protected Merger $merger;
    protected Folders $folders;
    protected Logger $logger;
    
    /**
     * Method __construct
     *
     * @param Config  $config  config
     * @param Merger  $merger  merger
     * @param Folders $folders folders
     * @param Logger  $logger  logger
     */
    public function __construct(
        Config $config,
        Merger $merger,
        Folders $folders,
        Logger $logger
    ) {
        $this->config = $config;
        $this->merger = $merger;
        $this->folders = $folders;
        $this->logger = $logger;
    }
    
    /**
     * Method loadRoutes
     *
     * @param string[] $includes             includes
     * @param string   $routeCacheFilePrefix routeCacheFilePrefix
     *
     * @return mixed[]
     */
    public function loadRoutes(
        array $includes,
        string $routeCacheFilePrefix
    ): array {
        $routeCacheFile = $this->getRouteCacheFile($routeCacheFilePrefix);
        if (!file_exists($routeCacheFile)
            || ($this->config->getEnv() === 'test'
            && $this->isSourceCodeChanged($routeCacheFile))
        ) {
            $routes = [
                'public' => [],
                'protected' => [],
                'private' => [],
            ];
            $export = null;
            while ($export !== var_export($routes, true)) {
                $export = var_export($routes, true);
                foreach ($includes as $include) {
                    $routes = $this->merger->merge(
                        $routes,
                        $this->includeRoutes($include)
                    );
                }
                $routes['protected'] = $this->merger->merge(
                    $routes['protected'],
                    $routes['public']
                );
                $routes['private'] = $this->merger->merge(
                    $routes['private'],
                    $routes['protected']
                );
                foreach ($includes as $include) {
                    $routes = $this->merger->merge(
                        $routes,
                        $this->includeRoutes($include)
                    );
                }
            }
            $exported = '<?php $routes = ' . $export . ';';
            if (!$exported) {
                throw new RuntimeException('Unable to export routes');
            }
            if (false === file_put_contents($routeCacheFile, $exported)) {
                throw new RuntimeException(
                    'Unable to write route cache: ' . $routeCacheFile
                );
            }
            clearstatcache(true, $routeCacheFile);
            $this->logger->info('Route cache is rebuilded.');
        }
        return $this->includeRoutes($routeCacheFile);
    }
    
    /**
     * Method getRouteCacheFile
     *
     * @param string $routeCacheFilePrefix routeCacheFilePrefix
     *
     * @return string
     */
    public function getRouteCacheFile(string $routeCacheFilePrefix): string
    {
        return self::ROUTE_CACHE_FILEPATH . "$routeCacheFilePrefix."
            . self::ROUTE_CACHE_FILENAME;
    }
    
    /**
     * Method includeRoutes
     *
     * @param string $include include
     *
     * @return mixed[]
     */
    protected function includeRoutes(string $include)
    {
        if (!file_exists($include)) {
            throw new RuntimeException("File not found: '$include'");
        }
        $routes = [];
        include $include;
        return $routes;
    }
    
    /**
     * Method isSourceCodeChanged
     *
     * @param string $routeCacheFile routeCacheFile
     *
     * @return bool
     */
    protected function isSourceCodeChanged(string $routeCacheFile): bool
    {
        $cacheTime = (new SplFileInfo($routeCacheFile))->getMTime();
        $files = ($this->folders)->getFilesRecursive(self::SOURCE_FOLDER);
        foreach ($files as $file) {
            if (preg_match('/\.php$/', $file->getFilename())
                && $file->getMTime() > $cacheTime
            ) {
                return true;
            }
        }
        return false;
    }
}
