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

use Madsoft\Library\Config;
use Madsoft\Library\Database;
use Madsoft\Library\Folders;
use Madsoft\Library\Mailer;
use Madsoft\Library\Router;
use RuntimeException;

/**
 * TestCleaner
 *
 * @category  PHP
 * @package   Madsoft\Library\Tester
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */
class TestCleaner
{
    protected Database $database;
    protected Folders $folders;
    protected Config $config;

    /**
     * Method __construct
     *
     * @param Database $database database
     * @param Folders  $folders  folders
     * @param Config   $config   config
     */
    public function __construct(
        Database $database,
        Folders $folders,
        Config $config
    ) {
        $this->database = $database;
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
        $this->database->delRows('content');
        $this->database->delRows('user');
        
        //        try {
        //            $this->database->delRows('ownership', []);
        //        } catch (MysqlNoAffectException $exception) {
        //            $this->logger->exception($exception);
        //        }
        
        $this->deleteMails();
        
        $this->deleteRouteCache();
    }
    
    /**
     * Method deleteMails
     *
     * @return self
     * @throws RuntimeException
     */
    public function deleteMails(): self
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
        clearstatcache(true);
        return $this;
    }
    
    /**
     * Method deleteRouteCache
     *
     * @return self
     * @throws RuntimeException
     */
    public function deleteRouteCache(): self
    {
        if (file_exists(Router::ROUTE_CACHE_FILE)
            && false === unlink(Router::ROUTE_CACHE_FILE)
        ) {
            throw new RuntimeException(
                'Unable to delete file: "' . Router::ROUTE_CACHE_FILE . '" '
            );
        }
        clearstatcache(true);
        return $this;
    }
}
