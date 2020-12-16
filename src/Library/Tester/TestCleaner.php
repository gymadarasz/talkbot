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

use Madsoft\Library\App\ApiApp;
use Madsoft\Library\App\WebApp;
use Madsoft\Library\Config;
use Madsoft\Library\Database;
use Madsoft\Library\Folders;
use Madsoft\Library\Mailer;
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
    protected ApiApp $apiApp;
    protected WebApp $webApp;

    /**
     * Method __construct
     *
     * @param Database $database database
     * @param Folders  $folders  folders
     * @param Config   $config   config
     * @param ApiApp   $apiApp   apiApp
     * @param WebApp   $webApp   webApp
     */
    public function __construct(
        Database $database,
        Folders $folders,
        Config $config,
        ApiApp $apiApp,
        WebApp $webApp
    ) {
        $this->database = $database;
        $this->folders = $folders;
        $this->config = $config;
        $this->apiApp = $apiApp;
        $this->webApp = $webApp;
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
        
        foreach ([
            $this->apiApp->getRouteCacheFile(),
            $this->webApp->getRouteCacheFile(),
        ] as $routeCacheFile) {
            $this->deleteRouteCache($routeCacheFile);
        }
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
     * @param string $routeCacheFile routeCacheFile
     *
     * @return self
     * @throws RuntimeException
     */
    public function deleteRouteCache(
        string $routeCacheFile
    ): self {
        if (file_exists($routeCacheFile)
            && false === unlink($routeCacheFile)
        ) {
            throw new RuntimeException(
                'Unable to delete file: "' . $routeCacheFile . '" '
            );
        }
        clearstatcache(true);
        return $this;
    }
}
