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

use Madsoft\Library\Config;
use Madsoft\Library\Folders;
use Madsoft\Library\Invoker;
use Madsoft\Library\Logger;
use Madsoft\Library\Mailer;
use Madsoft\Library\Merger;
use Madsoft\Library\Tester\Test;
use function count;

/**
 * MailerTest
 *
 * @category  PHP
 * @package   Madsoft\Tests\Library
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */
class MailerTest extends Test
{
    /**
     * Method testSend
     *
     * @return void
     *
     * @suppress PhanUnreferencedPublicMethod
     */
    public function testSend(): void
    {
        $invoker = new Invoker();
        $merger = new Merger();
        $config = new Config($invoker, $merger);
        $logger = new Logger();
        $mailer = new Mailer($config, $logger);
        $ret = $mailer->send('to@addr.com', 'hello123', 'body123');
        $this->assertTrue($ret);
        $folders = new Folders();
        $mailFileinfos = $folders
            ->getFilesRecursive(
                $config
                    ->get(Mailer::CONFIG_SECION)
                    ->get('save_mail_path')
            );
        $this->assertEquals(1, count($mailFileinfos));
        $filenames = array_keys($mailFileinfos);
        $this->assertStringContains('to@addr.com', (string)$filenames[0]);
        $this->assertStringContains('hello123', (string)$filenames[0]);
        $contents = file_get_contents((string)$filenames[0]);
        $this->assertNotEquals(false, $contents);
        $this->assertEquals('body123', (string)$contents);
    }
}
