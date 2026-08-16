<?php
/**
 * Email sending wrapper.
 * Stage: 7 - Real-world features (email delivery)
 */

require_once __DIR__ . '/../config/mail.php';
require_once __DIR__ . '/../vendor/phpmailer/src/Exception.php';
require_once __DIR__ . '/../vendor/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../vendor/phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

/**
 * Sends an HTML email. Returns true on success, false on any failure
 * (bad/placeholder SMTP credentials, network issue, etc.) - callers should
 * treat a false return as "email didn't go out" and handle gracefully
 * rather than crashing the request.
 */
function send_email(string $to, string $subject, string $htmlBody): bool
{
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = SMTP_SECURE;
        $mail->Port       = SMTP_PORT;

        $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
        $mail->addAddress($to);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;
        $mail->AltBody  = strip_tags($htmlBody);

        $mail->send();
        return true;
    } catch (PHPMailerException $e) {
        log_action(null, 'email_failed', "to={$to} subject=\"{$subject}\" error=" . $mail->ErrorInfo);
        return false;
    }
}
