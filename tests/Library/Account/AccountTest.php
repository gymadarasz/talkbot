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
use Madsoft\Library\Json;
use Madsoft\Library\Mailer;
use Madsoft\Library\Router;
use Madsoft\Library\Tester\ApiTest;
use Madsoft\Library\Tester\TestCleaner;
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
 *
 * @suppress PhanUnreferencedClass
 */
class AccountTest extends ApiTest
{
    const EMAIL = 'tester@testing.com';
    const PASSWORD_FIRST = 'First1234!';
    const PASSWORD = 'Pass1234!';
    
    /**
     * Variable $routes
     *
     * @var string[]
     */
    protected array $routes = [
        __DIR__ . '/../../../src/Library/Account/routes.php',
    ];
        
    protected Folders $folders;
    protected Database $database;
    protected Config $config;

    /**
     * Method __construct
     *
     * @param Router   $router   router
     * @param Invoker  $invoker  invoker
     * @param Json     $json     json
     * @param Folders  $folders  folders
     * @param Database $database database
     * @param Config   $config   config
     */
    public function __construct(
        Router $router,
        Invoker $invoker,
        Json $json,
        Folders $folders,
        Database $database,
        Config $config
    ) {
        parent::__construct($router, $invoker, $json);
        $this->folders = $folders;
        $this->database = $database;
        $this->config = $config;
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
        $this->canSeeLoginWorks(self::EMAIL, self::PASSWORD_FIRST);
        $this->canSeeLogoutWorks();
        //        $this->canSeeResetPasswordTokenFails();
        $this->canSeeResetPasswordRequestFails();
        $this->canSeeResetPasswordWorks();
        $this->canSeeNewPasswordFails();
        $this->canSeeNewPasswordWrongTokenFails();
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
            'q=login',
            [
                'csrf' => $this->getCsrf(),
            //                'email' => self::EMAIL,
            //                'password' => self::PASSWORD,
            ]
        );
        $this->assertStringContains('Login failed', $contents);
        
        $contents = $this->post(
            'q=login',
            [
                'csrf' => $this->getCsrf(),
                'email' => self::EMAIL,
            //                'password' => self::PASSWORD,
            ]
        );
        $this->assertStringContains('Login failed', $contents);
        
        $contents = $this->post(
            'q=login',
            [
                'csrf' => $this->getCsrf(),
            //                'email' => self::EMAIL,
                'password' => self::PASSWORD_FIRST,
            ]
        );
        $this->assertStringContains('Login failed', $contents);
        
        $contents = $this->post(
            'q=login',
            [
                'csrf' => $this->getCsrf(),
                'email' => '',
                'password' => '',
            ]
        );
        $this->assertStringContains('Login failed', $contents);
        
        $contents = $this->post(
            'q=login',
            [
                'csrf' => $this->getCsrf(),
                'email' => self::EMAIL,
                'password' => self::PASSWORD_FIRST,
            ]
        );
        $this->assertStringContains('Login failed', $contents);
        
        $response = $this->post(
            'q=login'
                . '&csrf=' . $this->getCsrf(),
            [
                'email' => 'wrongemail@example.com',
                'password' => 'BadPassword'
            ]
        );
        $results = $this->json->decode($response);
        $this->assertTrue(
            in_array('Login failed', $results['messages']['error'], true)
        );
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
            'q=registry',
            [
                'csrf' => $this->getCsrf(),
            //                'email' => '',
            //                'email_retype' => '',
            //                'password' => '',
            ]
        );
        $this->assertStringContains('Invalid registration data', $contents);
        $this->assertStringContains('Invalid email format', $contents);
        $this->assertStringContains('Invalid password', $contents);
        
        $contents = $this->post(
            'q=registry',
            [
                'csrf' => $this->getCsrf(),
                'email' => '',
                'email_retype' => '',
                'password' => '',
            ]
        );
        $this->assertStringContains('Invalid registration data', $contents);
        $this->assertStringContains('Invalid email format', $contents);
        $this->assertStringContains('Invalid password', $contents);
        
        $contents = $this->post(
            'q=registry',
            [
                'csrf' => $this->getCsrf(),
                'email' => 'itisnotvalid',
                'email_retype' => '',
                'password' => '',
            ]
        );
        $this->assertStringContains('Invalid registration data', $contents);
        $this->assertStringContains('Invalid email format', $contents);
        $this->assertStringContains('Invalid password', $contents);
        
        $contents = $this->post(
            'q=registry',
            [
                'csrf' => $this->getCsrf(),
                'email' => 'valid@email.com',
                'email_retype' => 'wrong@retype.com',
                'password' => '',
            ]
        );
        $this->assertStringContains('Invalid registration data', $contents);
        $this->assertStringContains('Doesn\'t match', $contents);
        $this->assertStringContains('Invalid password', $contents);
        
        $contents = $this->post(
            'q=registry',
            [
                'csrf' => $this->getCsrf(),
                'email' => 'valid@email.com',
                'email_retype' => 'valid@email.com',
                'password' => 'short',
            ]
        );
        $this->assertStringContains('Invalid registration data', $contents);
        $this->assertStringContains('Invalid password', $contents);
        
        $contents = $this->post(
            'q=registry',
            [
                'csrf' => $this->getCsrf(),
                'email' => 'valid@email.com',
                'email_retype' => 'valid@email.com',
                'password' => 'longbutdoesnothavenumber',
            ]
        );
        $this->assertStringContains('Invalid registration data', $contents);
        $this->assertStringContains('Invalid password', $contents);
        
        $contents = $this->post(
            'q=registry',
            [
                'csrf' => $this->getCsrf(),
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
     * @param string $email    email
     * @param string $password password
     *
     * @return void
     */
    protected function canSeeRegistryWorks(
        string $email = self::EMAIL,
        string $password = self::PASSWORD_FIRST
    ): void {
        $contents = $this->post(
            'q=registry',
            [
                'csrf' => $this->getCsrf(),
                'email' => $email,
                'email_retype' => $email,
                'password' => $password,
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
        $this->invoker->getInstance(TestCleaner::class)->deleteMails();
        $contents = $this->get(
            'q=resend&csrf=' . $this->getCsrf()
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
            'q=registry',
            [
                'csrf' => $this->getCsrf(),
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
     * @param string $email email
     *
     * @return void
     * @throws RuntimeException
     */
    protected function canSeeActivationMail(string $email = self::EMAIL): void
    {
        $emailFilename = $this->getLastEmailFilename();
        $this->assertStringContains($email, $emailFilename);
        $this->assertStringContains('Activate your account', $emailFilename);
        
        $user = $this->database->getRow(
            'user',
            ['token'],
            '',
            ['email' => $email]
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
            'q=activate&csrf=' . $this->getCsrf()
        );
        $this->assertStringContains('Account activation failed', $contents);
        $this->assertStringContains('Mandatory', $contents);
        
        $contents = $this->get(
            'q=activate&token=wrong-token'
                . '&csrf=' . $this->getCsrf()
        );
        $this->assertStringContains('Invalid token', $contents);
    }
    
    /**
     * Method canSeeActivationWorks
     *
     * @param string $email email
     *
     * @return void
     */
    protected function canSeeActivationWorks(string $email = self::EMAIL): void
    {
        $user = $this->database->getRow(
            'user',
            ['token'],
            '',
            ['email' => $email]
        );
        $contents = $this->get(
            'q=activate&token=' . ($user['token'] ?? '')
                . '&csrf=' . $this->getCsrf()
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
            '',
            ['email' => self::EMAIL]
        );
        $contents = $this->get(
            'q=activate&token=' . ($user['token'] ?? '')
                . '&csrf=' . $this->getCsrf()
        );
        $this->assertStringContains('Invalid token', $contents);
    }
    
    /**
     * Method canSeeLoginWorks
     *
     * @param string $email    email
     * @param string $password password
     *
     * @return void
     */
    protected function canSeeLoginWorks(
        string $email = self::EMAIL,
        string $password = self::PASSWORD
    ): void {
        $contents = $this->post(
            'q=login'
                . '&csrf=' . $this->getCsrf(),
            [
                'csrf' => $this->getCsrf(),
                'email' => $email,
                'password' => $password,
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
            'q=logout'
                . '&csrf=' . $this->getCsrf()
        );
        $this->assertStringContains('Logout success', $contents);
    }
    
    /**
     * Method canSeeResetPasswordTokenFails
     *
     * @return void
     */
    //    protected function canSeeResetPasswordTokenFails(): void
    //    {
    //        $contents = $this->get(
    //            'q=password-reset'
    //        );
    //        $this->assertStringContains('Missing token', $contents);
    //
    //        $contents = $this->get(
    //            'q=password-reset&token=wrong'
    //        );
    //        $this->assertStringContains('Invalid token', $contents);
    //        // TODO removing or fixing csrf token handling
    //    }
    
    /**
     * Method canSeeResetPasswordRequestFails
     *
     * @return void
     */
    protected function canSeeResetPasswordRequestFails(): void
    {
        $contents = $this->post(
            'q=password-reset-request'
                . '&csrf=' . $this->getCsrf(),
            [
                'csrf' => $this->getCsrf(),
            //                'email' => 'nonexist@useremail.com',
            ]
        );
        $this->assertStringContains('Reset password request failed', $contents);
        
        $contents = $this->post(
            'q=password-reset-request'
                . '&csrf=' . $this->getCsrf(),
            [
                'csrf' => $this->getCsrf(),
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
            'q=password-reset-request'
                . '&csrf=' . $this->getCsrf(),
            [
                'csrf' => $this->getCsrf(),
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
        $contents = $this->post(
            'q=password-change&token=wron-token'
                . '&csrf=' . $this->getCsrf()
        );
        $this->assertStringContains('Password change failed', $contents);
        $this->assertStringContains('Mandatory', $contents);
        $this->assertStringContains('Invalid password', $contents);
        
        $user = $this->database->getRow(
            'user',
            ['token'],
            '',
            ['email' => self::EMAIL]
        );
        $token = $user['token'] ?? '';
        $contents = $this->post(
            'q=password-change&token=' . $token,
            [
                'csrf' => $this->getCsrf(),
            //                'password' => '',
            //                'password_retype' => '',
            ]
        );
        $this->assertStringContains('Password change failed', $contents);
        $this->assertStringContains('Mandatory', $contents);
        $this->assertStringContains('Invalid password', $contents);
        
        $contents = $this->post(
            'q=password-change&token=' . $token,
            [
                'csrf' => $this->getCsrf(),
                'password' => '',
            //                'password_retype' => '',
            ]
        );
        $this->assertStringContains('Password change failed', $contents);
        $this->assertStringContains('Mandatory', $contents);
        $this->assertStringContains('Invalid password', $contents);
        
        $contents = $this->post(
            'q=password-change&token=' . $token,
            [
                'csrf' => $this->getCsrf(),
                'password' => 'short',
                'password_retype' => '',
            ]
        );
        $this->assertStringContains('Password change failed', $contents);
        $this->assertStringContains('Invalid password', $contents);
        $this->assertStringContains("Doesn't match", $contents);
        
        $contents = $this->post(
            'q=password-change&token=' . $token,
            [
                'csrf' => $this->getCsrf(),
                'password' => 'longwithoutnumbers',
                'password_retype' => '',
            ]
        );
        $this->assertStringContains('Password change failed', $contents);
        $this->assertStringContains('Invalid password', $contents);
        $this->assertStringContains("Doesn't match", $contents);
        
        $contents = $this->post(
            'q=password-change&token=' . $token,
            [
                'csrf' => $this->getCsrf(),
                'password' => 'withoutspecchar1234',
                'password_retype' => '',
            ]
        );
        $this->assertStringContains('Password change failed', $contents);
        $this->assertStringContains('Invalid password', $contents);
        $this->assertStringContains("Doesn't match", $contents);
    }
    
    /**
     * Method canSeeNewPasswordWrongTokenFails
     *
     * @return void
     */
    protected function canSeeNewPasswordWrongTokenFails(): void
    {
        $user = $this->database->getRow(
            'user',
            ['token'],
            '',
            ['email' => self::EMAIL]
        );
        $contents = $this->post(
            'q=password-change',
            [
                'csrf' => $this->getCsrf(),
                'token' => $user['token'].'wrong!!',
                'password' => 'Asd123!@#',
                'password_retype' => 'Asd123!@#'
            ],
        );
        $this->assertStringContains('User not found at the given token', $contents);
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
            '',
            ['email' => self::EMAIL]
        );
        $contents = $this->post(
            'q=password-change&token=' . ($user['token'] ?? ''),
            [
                'csrf' => $this->getCsrf(),
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
