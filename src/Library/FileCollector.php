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

/**
 * FileCollector
 *
 * @category  PHP
 * @package   Madsoft\Library
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */
class FileCollector
{
    /**
     * Variable $jsFiles
     *
     * @var string[]
     */
    protected array $jsFiles = [];
    
    /**
     * Variable $cssFiles
     *
     * @var string[]
     */
    protected array $cssFiles = [];
    
    protected Config $config;
    
    /**
     * Method __construct
     *
     * @param Config $config config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }
    
    /**
     * Method addJsFile
     *
     * @param string $jsFile jsFile
     *
     * @return self
     */
    public function addJsFile(string $jsFile): self
    {
        if (!preg_match('/.js$/', $jsFile)) {
            throw new RuntimeException(
                "Invalid JavaScript file extension: '$jsFile'"
            );
        }
        $url = $this->pathToUrl($jsFile);
        if (!in_array($url, $this->jsFiles, true)) {
            $this->jsFiles[] = $url;
        }
        return $this;
    }
    
    /**
     * Method getJsFiles
     *
     * @return string[]
     */
    public function getJsFiles(): array
    {
        return $this->jsFiles;
    }
    
    /**
     * Method addCssFile
     *
     * @param string $cssFile cssFile
     *
     * @return self
     *
     * @suppress PhanUnreferencedPublicMethod
     */
    public function addCssFile(string $cssFile): self
    {
        if (!preg_match('/.css$/', $cssFile)) {
            throw new RuntimeException(
                "Invalid StyleSheet file extension: '$cssFile'"
            );
        }
        $url = $this->pathToUrl($cssFile);
        if (!in_array($url, $this->cssFiles, true)) {
            $this->cssFiles[] = $url;
        }
        return $this;
    }
    
    /**
     * Method getCssFiles
     *
     * @return string[]
     */
    public function getCssFiles(): array
    {
        return $this->cssFiles;
    }
    
    /**
     * Method pathToUrl
     *
     * @param string $filename filename
     *
     * @return string
     */
    protected function pathToUrl(string $filename): string
    {
        $realpath = realpath($filename);
        if (!$realpath) {
            throw new RuntimeException("File not found: '$filename'");
        }
        $pattern = '/^' . str_replace(
            '/',
            '\/',
            $this->config->get('Site')->get('path_match_dir')
        ) . '/';
        $ret = preg_replace(
            $pattern,
            $this->config->get('Site')->get('path_match_url'),
            $realpath
        );
        if (null === $ret) {
            throw new RuntimeException(
                "Invalid filename given, pattern, path_match_url or path_match_url "
                    . "in config or realpath was wrong: pattern:'$pattern', "
                    . "realpath: '$realpath'"
            );
        }
        return $ret;
    }
}
