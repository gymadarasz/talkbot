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

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * Folders
 *
 * @category  PHP
 * @package   Madsoft\Library
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */
class Folders
{
    /**
     * Method getFilesRecursive
     *
     * @param string $path     path
     * @param int    $mode     mode
     * @param int    $flags    flags
     * @param int    $dirflags dirflags
     *
     * @return SplFileInfo[]
     */
    public function getFilesRecursive(
        string $path,
        int $mode =  RecursiveIteratorIterator::LEAVES_ONLY,
        int $flags = 0,
        int $dirflags = FilesystemIterator::KEY_AS_PATHNAME |
        FilesystemIterator::CURRENT_AS_FILEINFO
    ): array {
        $diri = new RecursiveDirectoryIterator($path, $dirflags);
        $iterator = new RecursiveIteratorIterator($diri, $mode, $flags);
        $files = [];
        foreach ($iterator as $key => $file) {
            $filename = $file->getFilename();
            if ($filename == '.' || $filename == '..') {
                continue;
            }
            $files[$key] = $file;
        }
        return $files;
    }
}
