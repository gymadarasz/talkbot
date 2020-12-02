<?php declare(strict_types = 1);

/**
 * PHP version 7.4
 *
 * @category  PHP
 * @package   Madsoft\Tests\Library\Account
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */

namespace Madsoft\Tests\Library\Account;

use DiDom\Document;
use DOMElement;
use Madsoft\Library\Config;
use Madsoft\Library\Database;
use Madsoft\Library\Folders;
use Madsoft\Library\Invoker;
use Madsoft\Library\Mailer;
use Madsoft\Library\Router;
use Madsoft\Library\Session;
use Madsoft\Library\Tester\Test;
use Madsoft\Tests\Library\LibraryTestCleaner;
use RuntimeException;
use SplFileInfo;

/**
 * AccountTest
 *
 * @category  PHP
 * @package   Madsoft\Tests\Library\Account
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyMethods)
 */
class AccountTest extends Test
{
    const EMAIL = 'tester@testing.com';
    const PASSWORD_FIRST = 'First1234!';
    const PASSWORD = 'Pass1234!';

    protected Invoker $invoker;
    protected Session $session;
    protected Folders $folders;
    protected Database $database;
    protected Config $config;
    protected LibraryTestCleaner $cleaner;
    protected Router $router;

    /**
     * Method __construct
     *
     * @param Invoker            $invoker  invoker
     * @param Session            $session  session
     * @param Folders            $folders  folders
     * @param Database           $database database
     * @param Config             $config   config
     * @param LibraryTestCleaner $cleaner  cleaner
     * @param Router             $router   router
     */
    public function __construct(
        Invoker $invoker,
        Session $session,
        Folders $folders,
        Database $database,
        Config $config,
        LibraryTestCleaner $cleaner,
        Router $router
    ) {
        $this->invoker = $invoker;
        $this->session = $session;
        $this->folders = $folders;
        $this->database = $database;
        $this->config = $config;
        $this->cleaner = $cleaner;
        $this->router = $router;
    }
    
    /**
     * Method getRoutes
     *
     * @return string[][][][]
     */
    protected function getRoutes(): array
    {
        return $this->router->loadRoutes(
            [
                __DIR__ . '/../../../src/Library/Account/routes.php',
                __DIR__ . '/../../../src/routes.api.php',
            ]
        );
    }
    
    /**
     * Method beforeAll
     *
     * @return void
     *
     * @suppress PhanUnreferencedPublicMethod
     */
    public function beforeAll(): void
    {
        $this->cleanup();
    }
    
    /**
     * Method afterAll
     *
     * @return void
     *
     * @suppress PhanUnreferencedPublicMethod
     */
    public function afterAll(): void
    {
        $this->cleanup();
    }
    
    /**
     * Method cleanup
     *
     * @return void
     */
    protected function cleanup(): void
    {
        $this->invoker->getInstance(LibraryTestCleaner::class)->deleteMails();
    }

    /**
     * Method testLogin
     *
     * @return void
     *
     * @suppress PhanUnreferencedPublicMethod
     */
    public function testAccount(): void
    {
        $this->canSeeLoginFails();
        $this->canSeeRegistryFails();
        $this->canSeeRegistryWorks();
        $this->canSeeActivationMail();
        $this->canSeeResendWorks();
        $this->canSeeActivationMail();
        $this->canSeeRegistryUserAlreadyRegisteredFail();
        $this->canSeeActivationFails();
        $this->canSeeActivationWorks();
        $this->canSeeActivationUserAlreadyActiveFail();
        $this->canSeeLoginWorks(self::PASSWORD_FIRST);
        $this->canSeeLogoutWorks();
        $this->canSeeResetPasswordTokenFails();
        $this->canSeeResetPasswordRequestFails();
        $this->canSeeResetPasswordWorks();
        $this->canSeeNewPasswordFails();
        $this->canSeeNewPassword();
        $this->canSeeNewPasswordWorks();
        $this->canSeeLoginWorks();
        $this->canSeeLogoutWorks();
    }
    
    /**
     * Method canSeeLoginFails
     *
     * @return void
     */
    protected function canSeeLoginFails(): void
    {
        $contents = $this->post(
            [$this, 'callApi'],
            [$this->getRoutes()],
            'q=login',
            [
                'csrf' => $this->session->get('csrf'),
            //                'email' => self::EMAIL,
            //                'password' => self::PASSWORD,
            ]
        );
        $this->assertStringContains('Login failed', $contents);
        
        $contents = $this->post(
            [$this, 'callApi'],
            [$this->getRoutes()],
            'q=login',
            [
                'csrf' => $this->session->get('csrf'),
                'email' => self::EMAIL,
            //                'password' => self::PASSWORD,
            ]
        );
        $this->assertStringContains('Login failed', $contents);
        
        $contents = $this->post(
            [$this, 'callApi'],
            [$this->getRoutes()],
            'q=login',
            [
                'csrf' => $this->session->get('csrf'),
            //                'email' => self::EMAIL,
                'password' => self::PASSWORD_FIRST,
            ]
        );
        $this->assertStringContains('Login failed', $contents);
        
        $contents = $this->post(
            [$this, 'callApi'],
            [$this->getRoutes()],
            'q=login',
            [
                'csrf' => $this->session->get('csrf'),
                'email' => '',
                'password' => '',
            ]
        );
        $this->assertStringContains('Login failed', $contents);
        
        $contents = $this->post(
            [$this, 'callApi'],
            [$this->getRoutes()],
            'q=login',
            [
                'csrf' => $this->session->get('csrf'),
                'email' => self::EMAIL,
                'password' => self::PASSWORD_FIRST,
            ]
        );
        $this->assertStringContains('Login failed', $contents);
    }
    
    /**
     * Method canSeeRegistryFails
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function canSeeRegistryFails(): void
    {
        $contents = $this->post(
            [$this, 'callApi'],
            [$this->getRoutes()],
            'q=registry',
            [
                'csrf' => $this->session->get('csrf'),
            //                'email' => '',
            //                'email_retype' => '',
            //                'password' => '',
            ]
        );
        $this->assertStringContains('Invalid registration data', $contents);
        $this->assertStringContains('Invalid email format', $contents);
        $this->assertStringContains('Invalid password', $contents);
        
        $contents = $this->post(
            [$this, 'callApi'],
            [$this->getRoutes()],
            'q=registry',
            [
                'csrf' => $this->session->get('csrf'),
                'email' => '',
                'email_retype' => '',
                'password' => '',
            ]
        );
        $this->assertStringContains('Invalid registration data', $contents);
        $this->assertStringContains('Invalid email format', $contents);
        $this->assertStringContains('Invalid password', $contents);
        
        $contents = $this->post(
            [$this, 'callApi'],
            [$this->getRoutes()],
            'q=registry',
            [
                'csrf' => $this->session->get('csrf'),
                'email' => 'itisnotvalid',
                'email_retype' => '',
                'password' => '',
            ]
        );
        $this->assertStringContains('Invalid registration data', $contents);
        $this->assertStringContains('Invalid email format', $contents);
        $this->assertStringContains('Invalid password', $contents);
        
        $contents = $this->post(
            [$this, 'callApi'],
            [$this->getRoutes()],
            'q=registry',
            [
                'csrf' => $this->session->get('csrf'),
                'email' => 'valid@email.com',
                'email_retype' => 'wrong@retype.com',
                'password' => '',
            ]
        );
        $this->assertStringContains('Invalid registration data', $contents);
        $this->assertStringContains('Doesn\'t match', $contents);
        $this->assertStringContains('Invalid password', $contents);
        
        $contents = $this->post(
            [$this, 'callApi'],
            [$this->getRoutes()],
            'q=registry',
            [
                'csrf' => $this->session->get('csrf'),
                'email' => 'valid@email.com',
                'email_retype' => 'valid@email.com',
                'password' => 'short',
            ]
        );
        $this->assertStringContains('Invalid registration data', $contents);
        $this->assertStringContains('Invalid password', $contents);
        
        $contents = $this->post(
            [$this, 'callApi'],
            [$this->getRoutes()],
            'q=registry',
            [
                'csrf' => $this->session->get('csrf'),
                'email' => 'valid@email.com',
                'email_retype' => 'valid@email.com',
                'password' => 'longbutdoesnothavenumber',
            ]
        );
        $this->assertStringContains('Invalid registration data', $contents);
        $this->assertStringContains('Invalid password', $contents);
        
        $contents = $this->post(
            [$this, 'callApi'],
            [$this->getRoutes()],
            'q=registry',
            [
                'csrf' => $this->session->get('csrf'),
                'email' => 'valid@email.com',
                'email_retype' => 'valid@email.com',
                'password' => 'nospecchar123',
            ]
        );
        $this->assertStringContains('Invalid registration data', $contents);
        $this->assertStringContains('Invalid password', $contents);
    }

    /**
     * Method canSeeRegistryWorks
     *
     * @return void
     */
    protected function canSeeRegistryWorks(): void
    {
        $contents = $this->post(
            [$this, 'callApi'],
            [$this->getRoutes()],
            'q=registry',
            [
                'csrf' => $this->session->get('csrf'),
                'email' => self::EMAIL,
                'email_retype' => self::EMAIL,
                'password' => self::PASSWORD_FIRST,
            ]
        );
        //$this->assertStringContains('Activate your account', $contents);
        $this->assertStringContains('We sent an activation email', $contents);
    }
    
    /**
     * Method canSeeResendWorks
     *
     * @return void
     */
    protected function canSeeResendWorks(): void
    {
        $this->cleaner->deleteMails();
        $contents = $this->get(
            [$this, 'callApi'],
            [$this->getRoutes()],
            'q=resend'
        );
        //        $this->assertStringContains('Activate your account', $contents);
        $this->assertStringContains('We re-sent an activation email', $contents);
    }
    
    
    /**
     * Method canSeeRegistryUserAlreadyRegisteredFail
     *
     * @return void
     */
    protected function canSeeRegistryUserAlreadyRegisteredFail(): void
    {
        $contents = $this->post(
            [$this, 'callApi'],
            [$this->getRoutes()],
            'q=registry',
            [
                'csrf' => $this->session->get('csrf'),
                'email' => self::EMAIL,
                'email_retype' => self::EMAIL,
                'password' => self::PASSWORD_FIRST,
            ]
        );
        $this->assertStringContains('Email address already registered', $contents);
    }
    
    /**
     * Method canSeeActivationMail
     *
     * @return void
     */
    protected function canSeeActivationMail(): void
    {
        $emailFilename = $this->getLastEmailFilename();
        $this->assertStringContains(self::EMAIL, $emailFilename);
        $this->assertStringContains('Activate your account', $emailFilename);
        
        $user = $this->database->getRow(
            'user',
            ['token'],
            ['email' => self::EMAIL]
        );
        $activationLink = $this->config->get('Site')->get('base')
                . '?q=activate&token=' . ($user['token'] ?? '');
        $links = (new Document($emailFilename, true))->find('a');
        $found = false;
        foreach ($links as $link) {
            if ($link instanceof DOMElement) {
                throw new RuntimeException('Invalid DOM context');
            }
            $href = $link->attr('href');
            if ($href === $activationLink) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found);
    }
    
    /**
     * Method canSeeActivationFails
     *
     * @return void
     */
    protected function canSeeActivationFails(): void
    {
        $contents = $this->get(
            [$this, 'callApi'],
            [$this->getRoutes()],
            'q=activate'
        );
        $this->assertStringContains('Account activation failed', $contents);
        $this->assertStringContains('Mandatory', $contents);
        
        $contents = $this->get(
            [$this, 'callApi'],
            [$this->getRoutes()],
            'q=activate&token=wrong-token'
        );
        $this->assertStringContains('Invalid token', $contents);
    }
    
    /**
     * Method canSeeActivationWorks
     *
     * @return void
     */
    protected function canSeeActivationWorks(): void
    {
        $user = $this->database->getRow(
            'user',
            ['token'],
            ['email' => self::EMAIL]
        );
        $contents = $this->get(
            [$this, 'callApi'],
            [$this->getRoutes()],
            'q=activate&token=' . ($user['token'] ?? '')
        );
        $this->assertStringContains('Account is now activated', $contents);
    }
    
    /**
     * Method canSeeActivationUserAlreayActiveFail
     *
     * @return void
     */
    protected function canSeeActivationUserAlreadyActiveFail(): void
    {
        $user = $this->database->getRow(
            'user',
            ['token'],
            ['email' => self::EMAIL]
        );
        $contents = $this->get(
            [$this, 'callApi'],
            [$this->getRoutes()],
            'q=activate&token=' . ($user['token'] ?? '')
        );
        $this->assertStringContains('Invalid token', $contents);
    }
    
    /**
     * Method canSeeLoginWorks
     *
     * @param string|null $password password
     *
     * @return void
     */
    protected function canSeeLoginWorks(?string $password = null): void
    {
        $contents = $this->post(
            [$this, 'callApi'],
            [$this->getRoutes()],
            'q=login',
            [
                'csrf' => $this->session->get('csrf'),
                'email' => $this::EMAIL,
                'password' => null === $password ? $this::PASSWORD : $password,
            ]
        );
        $this->assertStringContains('Login success', $contents);
    }
    
    /**
     * Method canSeeLogoutWorks
     *
     * @return void
     */
    protected function canSeeLogoutWorks(): void
    {
        $contents = $this->get(
            [$this, 'callApi'],
            [$this->getRoutes()],
            'q=logout'
        );
        $this->assertStringContains('Logout success', $contents);
    }
    
    /**
     * Method canSeeResetPasswordTokenFails
     *
     * @return void
     */
    protected function canSeeResetPasswordTokenFails(): void
    {
        $contents = $this->get(
            [$this, 'callApi'],
            [$this->getRoutes()],
            'q=password-reset'
        );
        $this->assertStringContains('Missing token', $contents);
        
        $contents = $this->get(
            [$this, 'callApi'],
            [$this->getRoutes()],
            'q=password-reset&token=wrong'
        );
        $this->assertStringContains('Invalid token', $contents);
        // TODO check if correct form exists
    }
    
    /**
     * Method canSeeResetPasswordRequestFails
     *
     * @return void
     */
    protected function canSeeResetPasswordRequestFails(): void
    {
        $contents = $this->post(
            [$this, 'callApi'],
            [$this->getRoutes()],
            'q=password-reset-request',
            [
                'csrf' => $this->session->get('csrf'),
            //                'email' => 'nonexist@useremail.com',
            ]
        );
        $this->assertStringContains('Reset password request failed', $contents);
        
        $contents = $this->post(
            [$this, 'callApi'],
            [$this->getRoutes()],
            'q=password-reset-request',
            [
                'csrf' => $this->session->get('csrf'),
                'email' => 'nonexist@useremail.com',
            ]
        );
        $this->assertStringContains('Email address not found', $contents);
    }
    
    /**
     * Method canSeeResetPasswordWorks
     *
     * @return void
     */
    protected function canSeeResetPasswordWorks(): void
    {
        $contents = $this->post(
            [$this, 'callApi'],
            [$this->getRoutes()],
            'q=password-reset-request',
            [
                'csrf' => $this->session->get('csrf'),
                'email' => self::EMAIL,
            ]
        );
        $this->assertStringContains('email sent', $contents);
    }
    
    /**
     * Method canSeeNewPasswordFails
     *
     * @return void
     */
    protected function canSeeNewPasswordFails(): void
    {
        $contents = $this->get(
            [$this, 'callApi'],
            [$this->getRoutes()],
            'q=password-reset&token=wron-token'
        );
        $this->assertStringContains('Invalid token', $contents);
        
        $user = $this->database->getRow(
            'user',
            ['token'],
            ['email' => self::EMAIL]
        );
        $token = $user['token'] ?? '';
        $contents = $this->post(
            [$this, 'callApi'],
            [$this->getRoutes()],
            'q=password-change&token=' . $token,
            [
                'csrf' => $this->session->get('csrf'),
            //                'password' => '',
            //                'password_retype' => '',
            ]
        );
        $this->assertStringContains('Password change failed', $contents);
        $this->assertStringContains('Mandatory', $contents);
        $this->assertStringContains('Invalid password', $contents);
        
        $contents = $this->post(
            [$this, 'callApi'],
            [$this->getRoutes()],
            'q=password-change&token=' . $token,
            [
                'csrf' => $this->session->get('csrf'),
                'password' => '',
            //                'password_retype' => '',
            ]
        );
        $this->assertStringContains('Password change failed', $contents);
        $this->assertStringContains('Mandatory', $contents);
        $this->assertStringContains('Invalid password', $contents);
        
        $contents = $this->post(
            [$this, 'callApi'],
            [$this->getRoutes()],
            'q=password-change&token=' . $token,
            [
                'csrf' => $this->session->get('csrf'),
                'password' => 'short',
                'password_retype' => '',
            ]
        );
        $this->assertStringContains('Password change failed', $contents);
        $this->assertStringContains('Invalid password', $contents);
        $this->assertStringContains("Doesn't match", $contents);
        
        $contents = $this->post(
            [$this, 'callApi'],
            [$this->getRoutes()],
            'q=password-change&token=' . $token,
            [
                'csrf' => $this->session->get('csrf'),
                'password' => 'longwithoutnumbers',
                'password_retype' => '',
            ]
        );
        $this->assertStringContains('Password change failed', $contents);
        $this->assertStringContains('Invalid password', $contents);
        $this->assertStringContains("Doesn't match", $contents);
        
        $contents = $this->post(
            [$this, 'callApi'],
            [$this->getRoutes()],
            'q=password-change&token=' . $token,
            [
                'csrf' => $this->session->get('csrf'),
                'password' => 'withoutspecchar1234',
                'password_retype' => '',
            ]
        );
        $this->assertStringContains('Password change failed', $contents);
        $this->assertStringContains('Invalid password', $contents);
        $this->assertStringContains("Doesn't match", $contents);
    }
    
    /**
     * Method canSeeNewPassword
     *
     * @return void
     */
    protected function canSeeNewPassword(): void
    {
        $user = $this->database->getRow(
            'user',
            ['token'],
            ['email' => self::EMAIL]
        );
        $contents = $this->get(
            [$this, 'callApi'],
            [$this->getRoutes()],
            'q=password-reset&token=' . ($user['token'] ?? '')
        );
        $this->assertStringContains('Token matches', $contents);
        // TODO check if correct form exists
    }
    
    /**
     * Method canSeeNewPasswordWorks
     *
     * @return void
     */
    protected function canSeeNewPasswordWorks(): void
    {
        $user = $this->database->getRow(
            'user',
            ['token'],
            ['email' => self::EMAIL]
        );
        $contents = $this->post(
            [$this, 'callApi'],
            [$this->getRoutes()],
            'q=password-change&token=' . ($user['token'] ?? ''),
            [
                'csrf' => $this->session->get('csrf'),
                'password' => self::PASSWORD,
                'password_retype' => self::PASSWORD,
            ]
        );
        $this->assertStringContains('Password is changed', $contents);
    }
    
    /**
     * Method getLastEmail
     *
     * @return SplFileInfo
     * @throws RuntimeException
     */
    protected function getLastEmail(): SplFileInfo
    {
        //        $dir = realpath($folder);
        //        if (false === $dir) {
        //            throw new RuntimeException('Folder not exists: ' . $folder);
        //        }
        $folder = $this->config->get(Mailer::CONFIG_SECION)->get('save_mail_path');
        $mails = $this->folders->getFilesRecursive($folder);
        $latest = null;
        foreach ($mails as $mail) {
            if ($mail->isDir()) {
                continue;
            }
            if (!$latest) {
                $latest = $mail;
                continue;
            }
            if ($latest->getMTime() < $mail->getMTime()) {
                $latest = $mail;
            }
        }
        if (!$latest) {
            throw new RuntimeException(
                'Mail file is not found in folder: ' . $folder
            );
        }
        return $latest;
    }
    
    /**
     * Method getLastEmailFilename
     *
     * @return string
     */
    protected function getLastEmailFilename(): string
    {
        $lastEmail = $this->getLastEmail();
        return $lastEmail->getPathname(); // $lastEmail->getFilename();
    }
    
    /**
     * Method getLastEmailContents
     *
     * @return string
     * @throws RuntimeException
     *
     * @suppress PhanUnreferencedProtectedMethod
     */
    protected function getLastEmailContents(): string
    {
        $mailfile = $this->getLastEmailFilename();
        $contents = file_get_contents($mailfile);
        if (false === $contents) {
            throw new RuntimeException('Unable to read: ' . $mailfile);
        }
        return $contents;
    }
}
