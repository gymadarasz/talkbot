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
    //        'csrf',
            'csrfgen',
        'base',
        'tplReservedVarKey',
        'tplReservedVarValue',
    ];
    
    const DEFAULT_HTML_VIEW_TEMPLATE = true;
    const DEFAULT_ENCODER = 'htmlentities';
    
    /**
     * Variable $vars
     *
     * @var mixed[]
     */
    public array $vars;
    
    protected bool $htmlViewTemplate = self::DEFAULT_HTML_VIEW_TEMPLATE;
    
    protected ?string $encoder = self::DEFAULT_ENCODER;

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
    public function __construct(
        Config $config,
        Safer $safer,
        Csrf $csrf
    ) {
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
     * Method setEncoder
     *
     * @param string|null $encoder encoder
     *
     * @return self
     */
    public function setEncoder(?string $encoder = null): self
    {
        $this->encoder = $encoder;
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
        $that = $this;
        foreach ($this->safer->freez(
            static function ($value) use ($that) {
                if ($that->encoder) {
                    $encoder = $that->encoder;
                    if (!is_callable($encoder)) {
                        throw new RuntimeException(
                            "'$encoder' is not callable to encode template variables"
                        );
                    }
                    return $encoder((string)$value);
                }
                return (string)$value;
            },
            $data
        ) as $key => $value) {
            if (is_numeric($key)) {
                $this->setHtmlViewTemplate(self::DEFAULT_HTML_VIEW_TEMPLATE);
                $this->setEncoder(self::DEFAULT_ENCODER);
                throw new RuntimeException(
                    "Variable name can not be number: '$key'"
                );
            }
            if (in_array($key, self::RESERVED_VARS, true)) {
                $this->setHtmlViewTemplate(self::DEFAULT_HTML_VIEW_TEMPLATE);
                $this->setEncoder(self::DEFAULT_ENCODER);
                throw new RuntimeException(
                    "Variable name is reserved: '$key'"
                );
            }
            $this->vars[$key] = $value;
        }
        if ($this->htmlViewTemplate) {
            $this->vars['csrf'] = $this->csrfgen->get();
            $this->vars['base'] = $this->config->get('Site')->get('base');
        }
        ob_start();
        $this->includeTemplateFile($filename, $path);
        $contents = (string)ob_get_contents();
        ob_end_clean();
        $this->setHtmlViewTemplate(self::DEFAULT_HTML_VIEW_TEMPLATE);
        $this->setEncoder(self::DEFAULT_ENCODER);
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
    protected function includeTemplateFile(
        string $filename,
        ?string $path = null
    ): void {
        foreach ($this->vars as $tplReservedVarKey => $tplReservedVarValue) {
            $$tplReservedVarKey = $tplReservedVarValue;
        }
        unset($tplReservedVarKey);
        unset($tplReservedVarValue);
        include null === $path ?
            $this->getTemplateFile($filename) : ($path . $filename);
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
    
    /**
     * Method restrict
     *
     * @return void
     */
    public function restrict(): void
    {
    }
}
