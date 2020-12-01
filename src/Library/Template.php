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
 * Template
 *
 * @category  PHP
 * @package   Madsoft\Library
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */
class Template
{
    const TPL_PATH = __DIR__ . '/phtml/';
    const TPL_PATH_EXT = __DIR__ . '/phtml/';
            
    const RESERVED_VARS = [
        'vars',
        'safer',
        'filename',
        'csrf',
        'csrfgen',
        'base',
    ];
    
    /**
     * Variable $vars
     *
     * @var mixed[]
     */
    public array $vars;
    
    protected bool $htmlViewTemplate = true;

    protected Config $config;
    protected Safer $safer;
    protected Csrf $csrfgen;
    
    /**
     * Method __construct
     *
     * @param Config $config config
     * @param Safer  $safer  safer
     * @param Csrf   $csrf   csrf
     */
    public function __construct(Config $config, Safer $safer, Csrf $csrf)
    {
        $this->config = $config;
        $this->safer = $safer;
        $this->csrfgen = $csrf;
    }
    
    /**
     * Method setHtmlViewTemplate
     *
     * @param bool $htmlViewTemplate htmlViewTemplate
     *
     * @return self
     */
    public function setHtmlViewTemplate(bool $htmlViewTemplate): self
    {
        $this->htmlViewTemplate = $htmlViewTemplate;
        return $this;
    }
    
    /**
     * Method getVars
     *
     * @return mixed[]
     */
    public function getVars(): array
    {
        return $this->vars;
    }
   
    /**
     * Method process
     *
     * @param string      $filename filename
     * @param mixed[]     $data     data
     * @param string|null $path     path
     *
     * @return string
     * @throws RuntimeException
     */
    public function process(
        string $filename,
        array $data = [],
        ?string $path = null
    ): string {
        $this->vars = [];
        foreach ($this->safer->freez('htmlentities', $data) as $key => $value) {
            if (is_numeric($key)) {
                throw new RuntimeException(
                    "Variable name can not be number: '$key'"
                );
            }
            if (in_array($key, self::RESERVED_VARS, true)) {
                throw new RuntimeException(
                    "Variable name is reserved: '$key'"
                );
            }
            $this->vars[$key] = $value;
        }
        $this->vars['csrf'] = $this->csrfgen->get();
        if ($this->htmlViewTemplate) {
            $this->vars['base'] = $this->config->get('Site')->get('base');
        }
        ob_start();
        $this->include($filename, $path);
        $contents = (string)ob_get_contents();
        ob_end_clean();
        $this->setHtmlViewTemplate(true);
        return $contents;
    }
    
    /**
     * Method include
     *
     * @param string      $filename filename
     * @param string|null $path     path
     *
     * @return void
     *
     * @suppress PhanUnusedVariable
     * @suppress PhanPluginDollarDollar
     */
    protected function include(string $filename, ?string $path = null): void
    {
        foreach ($this->vars as $key => $value) {
            $$key = $value;
        }
        include null === $path ?
            $this->getTemplateFile($filename) : ($path . $filename);
    }
    
    /**
     * Method restrict
     *
     * @return void
     */
    public function restrict(): void
    {
    }
    
    /**
     * Method getTemplateFile
     *
     * @param string $tplfile tplfile
     *
     * @return string
     * @throws RuntimeException
     */
    public function getTemplateFile(string $tplfile): string
    {
        $fullpath = $this::TPL_PATH_EXT . $tplfile;
        if (!file_exists($fullpath)) {
            $fullpath = $this::TPL_PATH . $tplfile;
        }
        if (!file_exists($fullpath)) {
            throw new RuntimeException(
                'Template file not found: ' . $this::TPL_PATH_EXT . $tplfile .
                ' nor: ' . $fullpath
            );
        }
        return $fullpath;
    }
}
