<?php declare(strict_types = 1);

/**
 * PHP version 7.4
 *
 * @category  PHP
 * @package   Madsoft\Library\Testing
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */

namespace Madsoft\Library\Testing;

use Madsoft\Library\Config;
use Madsoft\Library\FileCollector;
use Madsoft\Library\Folders;
use Madsoft\Library\Mailer;
use Madsoft\Library\Template;
use RuntimeException;
use SplFileInfo;

/**
 * Testing
 *
 * @category  PHP
 * @package   Madsoft\Library\Testing
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */
class Testing
{
    const TPL_PATH = __DIR__ . '/phtml/';
    
    protected Template $template;
    protected Config $config;
    protected Folders $folders;
    protected FileCollector $fileCollector;

    /**
     * Method __construct
     *
     * @param Template      $template      template
     * @param Config        $config        config
     * @param Folders       $folders       folders
     * @param FileCollector $fileCollector fileCollector
     *
     * @throws RuntimeException
     */
    public function __construct(
        Template $template,
        Config $config,
        Folders $folders,
        FileCollector $fileCollector
    ) {
        $this->template = $template;
        $this->config = $config;
        $this->folders = $folders;
        $this->fileCollector = $fileCollector;
        
        if ($config->getEnv() !== 'test') {
            throw new RuntimeException(
                'This functionality available only in test environment'
            );
        }
    }
    
    /**
     * Method getMailsStringResponse
     *
     * @return string
     *
     * @suppress PhanUnreferencedPublicMethod
     */
    public function getMailsStringResponse(): string
    {
        return $this->template->setEncoder(null)->process(
            'testing-mails.phtml',
            $this->getMails(),
            $this::TPL_PATH
        );
    }
    
    /**
     * Method deleteMailsStringResponse
     *
     * @return string
     *
     * @suppress PhanUnreferencedPublicMethod
     */
    public function deleteMailsStringResponse(): string
    {
        return $this->template->setEncoder(null)->process(
            'testing-mails.phtml',
            $this->deleteMails(),
            $this::TPL_PATH
        );
    }
    
    /**
     * Method getMailFileInfos
     *
     * @return SplFileInfo[]
     */
    protected function getMailFileInfos(): array
    {
        return $this->folders
            ->getFilesRecursive(
                $this->config
                    ->get(Mailer::CONFIG_SECION)
                    ->get('save_mail_path')
            );
    }
    
    /**
     * Method getMails
     *
     * @return string[][]
     */
    protected function getMails(): array
    {
        $mailFileInfos = $this->getMailFileInfos();
        $results = [];
        foreach ($mailFileInfos as $mailFileInfo) {
            $results[] = $this->fileCollector->pathToUrl(
                $mailFileInfo->getPath() . '/' . $mailFileInfo->getFilename()
            );
        }
        return ['mails' => $results];
    }
    
    /**
     * Method deleteMails
     *
     * @return mixed[]
     */
    protected function deleteMails(): array
    {
        $mailFileInfos = $this->getMailFileInfos();
        $error = '';
        foreach ($mailFileInfos as $mailFileInfo) {
            if (unlink(
                $mailFileInfo->getPath() . '/' . $mailFileInfo->getFilename()
            )
            ) {
                continue;
            }
            $error = 'One or more email is not deleted';
        }
        $mails = $this->getMails();
        if ($error) {
            $mails['error'] = $error;
        }
        return $mails;
    }
}
