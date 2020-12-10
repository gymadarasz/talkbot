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
 * Config
 *
 * @category  PHP
 * @package   Madsoft\Library
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */
class Config extends Section
{
    const PATH = __DIR__ . '/config/';
    const PATH_EXT = __DIR__ . '/../../config/';
    
    protected ?Template $template = null;
    
    protected Invoker $invoker;
    
    /**
     * Method __construct
     *
     * @param Invoker $invoker invoker
     * @param Merger  $merger  merger
     */
    public function __construct(
        Invoker $invoker,
        Merger $merger
    ) {
        $this->invoker = $invoker;
        
        $env = $this->getEnv();
        
        $this->data = $this->readConfig($this::PATH . 'config.ini');
        $lib = $this->readConfig($this::PATH . 'config.' . $env . '.ini');
        $this->data = $merger->merge($this->data, $lib);
        $ext = $this->readConfig($this::PATH_EXT . 'config.' . $env . '.ini');
        $this->data = $merger->merge($this->data, $ext);
    }
    
    /**
     * Method getTemplate
     *
     * @return Template
     */
    protected function getTemplate(): Template
    {
        if (null === $this->template) {
            $this->template = new Template(
                $this,
                $this->invoker->getInstance(Safer::class),
                $this->invoker->getInstance(Csrf::class)
            );
        }
        return $this->template;
    }
    
    /**
     * Method getEnv
     *
     * @return string
     */
    public function getEnv(): string
    {
        $env = 'test';
        if (file_exists($this::PATH . 'env.php')) {
            include $this::PATH . 'env.php';
        }
        if (file_exists($this::PATH_EXT . 'env.php')) {
            include $this::PATH_EXT . 'env.php';
        }
        return $env;
    }
    
    /**
     * Method readConfig
     *
     * @param string $filename filename
     *
     * @return mixed[]
     * @throws RuntimeException
     */
    protected function readConfig(string $filename): array
    {
        if (!file_exists($filename)) {
            throw new RuntimeException('Config file not found: ' . $filename);
        }
        $inistr = $this
            ->getTemplate()
            ->setHtmlViewTemplate(false)
            ->setEncoder(null)
            ->process($filename, ['__DIR__' => __DIR__], '');
        $cfg = parse_ini_string($inistr, true);
        
        if (false === $cfg) {
            throw new RuntimeException('Config error: ' . $filename);
        }
        
        return $cfg;
    }
}
