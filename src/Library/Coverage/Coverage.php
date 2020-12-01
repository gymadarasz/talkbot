<?php declare(strict_types = 1);

/**
 * PHP version 7.4
 *
 * @category  PHP
 * @package   Madsoft\Library\Coverage
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */

namespace Madsoft\Library\Coverage;

use Madsoft\Library\Template;
use RuntimeException;

/**
 * Coverage
 *
 * @category  PHP
 * @package   Madsoft\Library\Coverage
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */
class Coverage
{
    /**
     * Variable $blacklist
     *
     * @var string[]
     */
    protected array $blacklist;
    
    protected Template $template;

    /**
     * Method __construct
     *
     * @param Template $template template
     */
    public function __construct(Template $template)
    {
        $this->template = $template;
    }
    
    /**
     * Method isStarted
     *
     * @return bool
     *
     * @suppress PhanUndeclaredFunction
     */
    public function isStarted(): bool
    {
        return xdebug_code_coverage_started();
    }
    
    /**
     * Method start
     *
     * @param string[] $blacklist blacklist
     *
     * @return void
     * @throws RuntimeException
     *
     * @suppress PhanUndeclaredConstant
     * @suppress PhanUndeclaredFunction
     * @suppress PhanUnreferencedPublicMethod
     */
    public function start(array $blacklist): void
    {
        $this->blacklist = $this->correctingBacklist($blacklist);
        
        xdebug_start_code_coverage(XDEBUG_CC_UNUSED | XDEBUG_CC_DEAD_CODE);
        
        if (!xdebug_code_coverage_started()) {
            throw new RuntimeException('Code coverage is not started.');
        }
    }
    
    /**
     * Method correctingBacklist
     *
     * @param string[] $blacklist blacklist
     *
     * @return string[]
     * @throws RuntimeException
     */
    protected function correctingBacklist(array $blacklist): array
    {
        foreach ($blacklist as &$pattern) {
            $realpath = realpath($pattern);
            if ($realpath && (file_exists($pattern) || is_dir($pattern))) {
                $pattern = $realpath;
                continue;
            }
            throw new RuntimeException(
                'Blacklist contains a non-existing element: "' . $pattern . '"'
            );
        }
        return $blacklist;
    }
    
    /**
     * Method stop
     *
     * @return void
     * @throws RuntimeException
     *
     * @suppress PhanUndeclaredFunction
     * @suppress PhanUnreferencedPublicMethod
     */
    public function stop(): void
    {
        xdebug_stop_code_coverage();
        if (xdebug_code_coverage_started()) {
            throw new RuntimeException('Code coverage is not stopped.');
        }
    }
    
    /**
     * Method getCoverageData
     *
     * @return int[][]
     * @throws RuntimeException
     *
     * @suppress PhanUndeclaredFunction
     */
    public function getCoverageData(): array
    {
        $data = xdebug_get_code_coverage();
        $results = [];
        foreach ($data as $file => $elem) {
            $black = false;
            foreach ($this->blacklist as $blackpath) {
                if (strpos($file, $blackpath) === 0) {
                    $black = true;
                    break;
                }
            }
            if (!$black) {
                $results[$file] = $elem;
            }
        }
        ksort($results);
        return $results;
    }
    
    /**
     * Method getPercentage
     *
     * @param int[][] $coverageData coverageData
     * @param int     $digits       digits
     *
     * @return float
     */
    public function getPercentage(array $coverageData, int $digits = 2): float
    {
        $all = 0;
        $covered = 0;
        foreach ($coverageData as $lines) {
            //            $all += count($lines);
            foreach ($lines as $info) {
                if ($info == 1) {
                    $all++;
                    $covered++;
                }
                if ($info == -1) {
                    $all++;
                }
            }
        }
        $percentage = (100 * $covered) / $all;
        if ($digits < 0) {
            return $percentage;
        }
        return (float)number_format($percentage, $digits, '.', '');
    }
    
    /**
     * Method getCoverageInfo
     *
     * @param int[][] $coverageData coverageData
     *
     * @return mixed[]
     * @throws RuntimeException
     */
    protected function getCoverageInfo(array $coverageData): array
    {
        $all = 0;
        $covered = 0;
        $contentsLines = [
            'percentage' => null,
            'files' => [],
        ];
        foreach ($coverageData as $file => $lines) {
            if (!is_string($file)) {
                throw new RuntimeException('Filename should be a string.');
            }
            $countLines = 0; //count($lines);
            //            $all += $countLines;
            if (!file_exists($file)) {
                throw new RuntimeException('File not found: ' . $file);
            }
            $contents = file_get_contents($file);
            if (false === $contents) {
                throw new RuntimeException('Unable to read file: ' . $file);
            }
            $contentsLines['files'][$file]['lines'] = explode("\n", $contents);
            foreach ($contentsLines['files'][$file]['lines'] as &$contentsLine) {
                $contentsLine = [
                    'code' => $contentsLine,
                    'info' => 0,
                ];
            }
            
            $contentsCovered = 0;
            foreach ($lines as $line => $info) {
                if ($info == 1) {
                    $all++;
                    $countLines++;
                    $covered++;
                    $contentsCovered++;
                }
                if ($info == -1) {
                    $all++;
                    $countLines++;
                }
                $contentsLines['files'][$file]['lines'][$line-1]['info'] = $info;
            }
            $contentsLines['files'][$file]['percentage'] = (100 * $contentsCovered)
                    / $countLines;
        }
        $contentsLines['percentage'] = (100 * $covered) / $all;
        return $contentsLines;
    }
    
    /**
     * Method generateCoverageInfo
     *
     * @param int[][] $coverageData coverageData
     *
     * @return string
     */
    public function generateCoverageInfo(array $coverageData): string
    {
        $coverageInfo = $this->getCoverageInfo($coverageData);
        return $this->template->process(
            'coverage.phtml',
            $coverageInfo,
            __DIR__ . '/phtml/'
        );
    }
}
