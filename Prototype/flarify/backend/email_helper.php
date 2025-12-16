<?php
/**
 * Email Helper for Flarify
 * 
 * This file provides email sending functionality using PHPMailer.
 * 
 * SETUP INSTRUCTIONS:
 * 1. Install PHPMailer via Composer: composer require phpmailer/phpmailer
 *    OR download from: https://github.com/PHPMailer/PHPMailer
 * 
 * 2. Configure SMTP settings below with your email provider
 * 
 * 3. Recommended email providers:
 *    - Gmail: smtp.gmail.com, port 587 (requires App Password)
 *    - SendGrid: smtp.sendgrid.net, port 587
 *    - Mailgun: smtp.mailgun.org, port 587
 *    - Outlook: smtp-mail.outlook.com, port 587
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// If using Composer (recommended)
// require '../vendor/autoload.php';

// If using manual installation, uncomment these:
// require '../PHPMailer/src/Exception.php';
// require '../PHPMailer/src/PHPMailer.php';
// require '../PHPMailer/src/SMTP.php';

/**
 * Email Configuration
 * IMPORTANT: Replace these with your actual SMTP credentials
 */
define('SMTP_HOST', 'smtp.gmail.com');          // Your SMTP server
define('SMTP_PORT', 587);                        // Usually 587 for TLS, 465 for SSL
define('SMTP_USERNAME', 'your-email@gmail.com'); // Your email address
define('SMTP_PASSWORD', 'your-app-password');    // Your email password or app password
define('SMTP_FROM_EMAIL', 'your-email@gmail.com');
define('SMTP_FROM_NAME', 'Flarify');
define('SMTP_ENCRYPTION', 'tls');                // 'tls' or 'ssl'

/**
 * Send an email
 * 
 * @param string $to Recipient email address
 * @param string $toName Recipient name
 * @param string $subject Email subject
 * @param string $body Email body (HTML)
 * @param string $altBody Plain text alternative (optional)
 * @return array ['success' => bool, 'message' => string]
 */
function sendEmail($to, $toName, $subject, $body, $altBody = '') {
    try {
        $mail = new PHPMailer(true);
        
        // Server settings
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_ENCRYPTION;
        $mail->Port       = SMTP_PORT;
        
        // Recipients
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($to, $toName);
        $mail->addReplyTo(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = $altBody ?: strip_tags($body);
        
        $mail->send();
        return ['success' => true, 'message' => 'Email sent successfully'];
    } catch (Exception $e) {
        error_log("Email sending failed: " . $mail->ErrorInfo);
        return ['success' => false, 'message' => "Email could not be sent. Mailer Error: {$mail->ErrorInfo}"];
    }
}

/**
 * Send password reset email
 * 
 * @param string $to Recipient email
 * @param string $name Recipient name
 * @param string $token Reset token
 * @return array Result of email send
 */
function sendPasswordResetEmail($to, $name, $token) {
    $resetUrl = "http://" . $_SERVER['HTTP_HOST'] . "/flarify%202/backend/reset.php?token=" . $token;
    
    $subject = "Reset Your Flarify Password";
    
    $body = "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #9B59FF, #7B3FF2); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
            .button { display: inline-block; padding: 15px 30px; background: #9B59FF; color: white; text-decoration: none; border-radius: 8px; font-weight: bold; margin: 20px 0; }
            .footer { text-align: center; padding: 20px; color: #666; font-size: 0.9rem; }
            .warning { background: #fff3cd; border: 1px solid #ffc107; padding: 15px; border-radius: 5px; margin: 20px 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Password Reset Request</h1>
            </div>
            <div class='content'>
                <p>Hi <strong>{$name}</strong>,</p>
                <p>We received a request to reset your password for your Flarify account.</p>
                <p>Click the button below to reset your password:</p>
                <p style='text-align: center;'>
                    <a href='{$resetUrl}' class='button'>Reset Password</a>
                </p>
                <div class='warning'>
                    <strong>⚠️ Important:</strong> This link will expire in 15 minutes for security reasons.
                </div>
                <p>If you didn't request this password reset, please ignore this email. Your password will remain unchanged.</p>
                <p>For security reasons, never share this link with anyone.</p>
                <p style='color: #666; font-size: 0.9rem; margin-top: 30px;'>
                    If the button doesn't work, copy and paste this link into your browser:<br>
                    <a href='{$resetUrl}'>{$resetUrl}</a>
                </p>
            </div>
            <div class='footer'>
                <p><strong>Flarify</strong> - Build, Innovate, Manage</p>
                <p>#GameItUp</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $altBody = "Hi {$name},\n\nWe received a request to reset your password.\n\nClick this link to reset your password (expires in 15 minutes):\n{$resetUrl}\n\nIf you didn't request this, please ignore this email.\n\n- Flarify Team";
    
    return sendEmail($to, $name, $subject, $body, $altBody);
}

/**
 * Send 2FA verification code email
 * 
 * @param string $to Recipient email
 * @param string $name Recipient name
 * @param string $code 6-digit verification code
 * @return array Result of email send
 */
function send2FAEmail($to, $name, $code) {
    $subject = "Your Flarify Verification Code";
    
    $body = "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #9B59FF, #7B3FF2); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; text-align: center; }
            .code { font-size: 36px; font-weight: bold; letter-spacing: 8px; color: #9B59FF; background: white; padding: 20px; border-radius: 10px; margin: 20px 0; }
            .footer { text-align: center; padding: 20px; color: #666; font-size: 0.9rem; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🔐 Verification Code</h1>
            </div>
            <div class='content'>
                <p>Hi <strong>{$name}</strong>,</p>
                <p>Here's your verification code:</p>
                <div class='code'>{$code}</div>
                <p>This code will expire in 10 minutes.</p>
                <p style='color: #999; font-size: 0.9rem;'>If you didn't request this code, please secure your account immediately.</p>
            </div>
            <div class='footer'>
                <p><strong>Flarify</strong> - Secure Gaming Platform</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $altBody = "Hi {$name},\n\nYour verification code is: {$code}\n\nThis code expires in 10 minutes.\n\n- Flarify Team";
    
    return sendEmail($to, $name, $subject, $body, $altBody);
}

/**
 * Send notification email
 * 
 * @param string $to Recipient email
 * @param string $name Recipient name
 * @param string $title Notification title
 * @param string $message Notification message
 * @param string $actionUrl Optional action URL
 * @param string $actionText Optional action button text
 * @return array Result of email send
 */
function sendNotificationEmail($to, $name, $title, $message, $actionUrl = '', $actionText = 'View Details') {
    $subject = "Flarify - " . $title;
    
    $actionButton = '';
    if ($actionUrl) {
        $actionButton = "<p style='text-align: center;'><a href='{$actionUrl}' class='button'>{$actionText}</a></p>";
    }
    
    $body = "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #9B59FF, #7B3FF2); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
            .button { display: inline-block; padding: 15px 30px; background: #9B59FF; color: white; text-decoration: none; border-radius: 8px; font-weight: bold; margin: 20px 0; }
            .footer { text-align: center; padding: 20px; color: #666; font-size: 0.9rem; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>{$title}</h1>
            </div>
            <div class='content'>
                <p>Hi <strong>{$name}</strong>,</p>
                <p>{$message}</p>
                {$actionButton}
            </div>
            <div class='footer'>
                <p><strong>Flarify</strong> - Build, Innovate, Manage</p>
                <p>#GameItUp</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $altBody = "Hi {$name},\n\n{$title}\n\n{$message}\n\n" . ($actionUrl ? "View: {$actionUrl}\n\n" : "") . "- Flarify Team";
    
    return sendEmail($to, $name, $subject, $body, $altBody);
}
?>
