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
use Madsoft\Library\Crud;
use Madsoft\Library\Folders;
use Madsoft\Library\Mailer;
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
    protected Crud $crud;
    protected Folders $folders;
    protected Config $config;

    /**
     * Method __construct
     *
     * @param Crud    $crud    crud
     * @param Folders $folders folders
     * @param Config  $config  config
     */
    public function __construct(Crud $crud, Folders $folders, Config $config)
    {
        $this->crud = $crud;
        $this->folders = $folders;
        $this->config = $config;
    }
    /**
     * Method cleanUp
     *
     * @return void
     */
    public function cleanUp(): void
    {
        $this->crud->delRows('user', []);
        $this->crud->delRows('ownership', []);
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
