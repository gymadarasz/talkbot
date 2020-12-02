<?php declare(strict_types = 1);

/**
 * PHP version 7.4
 *
 * @category  PHP
 * @package   Madsoft\Tests\Library
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */

namespace Madsoft\Tests\Library;

use Madsoft\Library\Cleaner;
use Madsoft\Library\Config;
use Madsoft\Library\Database;
use Madsoft\Library\Folders;
use Madsoft\Library\Mailer;
use Madsoft\Library\Mysql;
use Madsoft\Library\Throwier;
use RuntimeException;

/**
 * LibraryTestCleaner
 *
 * @category  PHP
 * @package   Madsoft\Tests\Library
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */
class LibraryTestCleaner implements Cleaner
{
    protected Database $database;
    protected Folders $folders;
    protected Config $config;
    protected Throwier $throwier;

    /**
     * Method __construct
     *
     * @param Database $database database
     * @param Folders  $folders  folders
     * @param Config   $config   config
     * @param Throwier $throwier throwier
     */
    public function __construct(
        Database $database,
        Folders $folders,
        Config $config,
        Throwier $throwier
    ) {
        $this->database = $database;
        $this->folders = $folders;
        $this->config = $config;
        $this->throwier = $throwier;
    }
    /**
     * Method cleanUp
     *
     * @return void
     */
    public function cleanUp(): void
    {
        try {
            $this->database->delRows('user', []);
        } catch (RuntimeException $exception) {
            if ($exception->getCode() !== Mysql::MYSQL_ERROR) {
                $this->throwier->throwPrevious($exception);
            }
        }
        
        try {
            $this->database->delRows('ownership', []);
        } catch (RuntimeException $exception) {
            if ($exception->getCode() !== Mysql::MYSQL_ERROR) {
                $this->throwier->throwPrevious($exception);
            }
        }
        
        $this->deleteMails();
    }
    
    /**
     * Method deleteMails
     *
     * @return void
     * @throws RuntimeException
     */
    public function deleteMails(): void
    {
        $mails = $this->folders->getFilesRecursive(
            $this->config->get(Mailer::CONFIG_SECION)->get('save_mail_path')
        );
        foreach ($mails as $mail) {
            if (!$mail->isDir()) {
                if (!unlink($mail->getPathname())) {
                    throw new RuntimeException(
                        'Unable to delete file: ' . $mail->getPathname()
                    );
                }
            }
        }
    }
}
