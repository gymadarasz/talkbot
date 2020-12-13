<?php declare(strict_types = 1);

/**
 * Mailer
 *
 * PHP version 7.4
 *
 * @category  PHP
 * @package   Madsoft\Library
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) all right reserved.
 * @link      this
 */

namespace Madsoft\Library;

use Exception;
use PHPMailer\PHPMailer\PHPMailer;
use RuntimeException;
use function date;
use function file_exists;
use function file_put_contents;
use function is_dir;
use function mkdir;
use function strip_tags;

/**
 * Mailer
 *
 * @category  PHP
 * @package   Madsoft\Library
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) all right reserved.
 * @link      this
 */
class Mailer
{
    const CONFIG_SECION = 'Mailer';
    
    protected Section $section;
    protected Logger $logger;

    /**
     * Method __construct
     *
     * @param Config $config config
     * @param Logger $logger logger
     */
    public function __construct(Config $config, Logger $logger)
    {
        $this->logger = $logger;
        $this->section = $config->get(self::CONFIG_SECION);
    }
    
    /**
     * Method send
     *
     * @param string $addressTo addressTo
     * @param string $subject   subject
     * @param string $body      body
     *
     * @return bool
     * @throws RuntimeException
     */
    public function send(
        string $addressTo,
        string $subject,
        string $body
    ): bool {
        // TODO: do not send to many emails in a time period!
        
        // Instantiation and passing `true` enables exceptions
        $mail = new PHPMailer(true);

        try {
            //Server settings
            
            // Enable verbose debug output
            //$mail->SMTPDebug = SMTP::DEBUG_SERVER;
            
            // Send using SMTP
            $mail->isSMTP();
            
            // Set the SMTP server to send through
            $mail->Host       = $this->section->get('host');
            
            // Enable SMTP authentication
            $mail->SMTPAuth   = true;
            
            // SMTP username
            $mail->Username   = $this->section->get('user');
            
            // SMTP password
            $mail->Password   = $this->section->get('secret');
            
            // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            
            // TCP port to connect to,
            // use 465 for `PHPMailer::ENCRYPTION_SMTPS` above
            $mail->Port       = $this->section->get('port');

            //Recipients
            $mail->setFrom(
                $this->section->get('system_email'),
                $this->section->get('system_from')
            );
            
            // Add a recipient
            $mail->addAddress($addressTo);
            
            $mail->addReplyTo(
                $this->section->get('noreply_email'),
                $this->section->get('noreply_name')
            );
            // $mail->addCC('cc@example.com');
            // $mail->addBCC('bcc@example.com');

            // Attachments
            
            // Add attachments
            // $mail->addAttachment('/var/tmp/file.tar.gz');
            //
            // // Optional name
            // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');

            // Content
            
            // Set email format to HTML
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->AltBody = strip_tags($body);
            if ($this->section->get('send_mail')) {
                $mail->send();
                $this->logger->info(
                    "Email sent to: $addressTo\nSubject: $subject\nBody:\n$body\n\n"
                );
            }
            if ($this->section->get('save_mail')) {
                $this->saveMail($addressTo, $subject, $body);
            }
            return true;
        } catch (Exception $e) {
            $this->logger->error(
                "Message could not be sent. Mailer Error: {$mail->ErrorInfo}\n"
                    . "Exception message: " . $e->getMessage() . "\n"
                    . "Trace:\n" . $e->getTraceAsString()
            );
            return false;
        }
    }
    
    /**
     * Method saveMail
     *
     * @param string $addressTo addressTo
     * @param string $subject   subject
     * @param string $body      body
     *
     * @return bool
     * @throws RuntimeException
     */
    protected function saveMail(
        string $addressTo,
        string $subject,
        string $body
    ): bool {
        if ((!file_exists($this->section->get('save_mail_path'))
            || !is_dir($this->section->get('save_mail_path')))
            && !mkdir(
                $this->section->get('save_mail_path'),
                $this->section->get('save_mail_mode'),
                true
            )
        ) {
            throw new RuntimeException(
                "Folder creation error: " . $this->section->get('save_mail_path')
            );
        }
        $fname = $this->section->get('save_mail_path') .
                        date("Y-m-d H-i-s") . " to $addressTo ($subject).html";
        if (file_put_contents($fname, $body)) {
            $this->logger->info("Email saved to: $fname");
            if (!chmod($fname, 0777)) {
                throw new RuntimeException(
                    "File permission is not set for file '$fname'"
                );
            }
            return true;
        }
        $this->logger->error("Message could not be saved to: $fname");
        return false;
    }
}
