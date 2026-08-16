<?php
/**
 * SMTP configuration for real email delivery (password reset, etc.)
 *
 * You MUST fill these in with real credentials for email to actually send.
 * Easiest options for a student/dev project:
 *
 *  Gmail:
 *    host = smtp.gmail.com, port = 587, secure = 'tls'
 *    user = your Gmail address
 *    pass = a 16-character "App Password" (NOT your normal Gmail password) -
 *           generate one at https://myaccount.google.com/apppasswords
 *           (requires 2-Step Verification to be enabled on the account)
 *
 *  Mailtrap (fake inbox for testing - emails never leave Mailtrap, good for dev):
 *    host = sandbox.smtp.mailtrap.io, port = 2525
 *    user/pass = from your Mailtrap inbox settings
 *
 * If these are left as placeholders, includes/mailer.php will fail to send
 * and auth/signin.php's Forgot Password tab will fall back to showing the
 * directly on screen instead (clearly marked as a dev-mode fallback).
 */

define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls'); // 'tls' or 'ssl'
define('SMTP_USER', 'your-email@gmail.com');
define('SMTP_PASS', 'your-16-char-app-password');

define('MAIL_FROM', SMTP_USER);
define('MAIL_FROM_NAME', 'My Store');
