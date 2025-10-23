<?php

namespace Angel\IapGroupProject\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class EmailService {
    private $smtpHost;
    private $smtpPort;
    private $username;
    private $password;
    private $encryption;
    private $fromAddress;
    private $fromName;

    public function __construct() {
        $this->smtpHost = $_ENV['MAIL_HOST'] ?? 'localhost';
        $this->smtpPort = $_ENV['MAIL_PORT'] ?? 587;
        $this->username = $_ENV['MAIL_USERNAME'] ?? '';
        $this->password = $_ENV['MAIL_PASSWORD'] ?? '';
        $this->encryption = $_ENV['MAIL_ENCRYPTION'] ?? 'tls';
        $this->fromAddress = $_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@gopuppygo.com';
        $this->fromName = $_ENV['MAIL_FROM_NAME'] ?? 'Go Puppy Go';
        
        // Debug logging
        error_log("EmailService config - Host: " . $this->smtpHost . ", Port: " . $this->smtpPort . ", User: " . $this->username);
    }

    public function sendVerificationEmail($email, $fullName, $verificationToken, $isAdmin = false, $adminAccessCode = null) {
        $subject = "Welcome to Go Puppy Go - Account Created Successfully";
        $verificationLink = $this->getBaseUrl() . "/verify-email.php?token=" . urlencode($verificationToken);
        
        $message = $this->buildVerificationEmailHtml($fullName, $verificationLink, $isAdmin, $adminAccessCode);
        
        return $this->sendEmail($email, $subject, $message);
    }

    private function buildVerificationEmailHtml($fullName, $verificationLink, $isAdmin, $adminAccessCode) {
        $adminSection = '';
        if ($isAdmin && $adminAccessCode) {
            $adminSection = '
                <div style="background-color: #fff3cd; border: 1px solid #ffeaa7; padding: 20px; margin: 20px 0; border-radius: 8px;">
                    <h3 style="color: #856404; margin: 0 0 10px 0;">🔐 Admin Access Information</h3>
                    <p style="margin: 0 0 10px 0; color: #856404;">
                        <strong>Your unique Admin Access Code:</strong>
                    </p>
                    <div style="background-color: #f8f9fa; padding: 15px; border-radius: 4px; font-family: monospace; font-size: 18px; font-weight: bold; text-align: center; color: #495057; border: 2px solid #dee2e6;">
                        ' . htmlspecialchars($adminAccessCode) . '
                    </div>
                    <p style="margin: 10px 0 0 0; color: #856404; font-size: 14px;">
                        <strong>Important:</strong> You will need this access code every time you log in as an administrator. Please save it securely.
                    </p>
                </div>';
        }

        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Account Created - Go Puppy Go</title>
        </head>
        <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
            <div style="background-color: #4472c4; color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0;">
                <h1 style="margin: 0; font-size: 28px;">🐶 Go Puppy Go</h1>
                <p style="margin: 10px 0 0 0; font-size: 16px;">Welcome to our community!</p>
            </div>
            
            <div style="background-color: #f8f9fa; padding: 30px; border-radius: 0 0 8px 8px; border: 1px solid #dee2e6;">
                <h2 style="color: #4472c4; margin: 0 0 20px 0; font-size: 24px;">Hello, ' . htmlspecialchars($fullName) . '</h2>
                
                <p style="font-size: 16px; margin: 20px 0;">Thank you for registering with Go Puppy Go! Your account has been created successfully.</p>
                
                <p style="font-size: 16px; margin: 20px 0;"><strong>Proceed to log in to the system.</strong></p>
                
                <div style="text-align: center; margin: 30px 0;">
                    <a href="' . $this->getBaseUrl() . '/login.php" 
                       style="background-color: #28a745; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block; font-size: 16px;">
                        🚀 Login Now
                    </a>
                </div>
                
                ' . $adminSection . '
                
                <div style="border-top: 1px solid #dee2e6; padding-top: 20px; margin-top: 30px; font-size: 14px; color: #6c757d;">
                    <p><strong>Security Notice:</strong> This verification link will expire in 24 hours for your security.</p>
                    <p>If you didn\'t create an account with Go Puppy Go, please ignore this email.</p>
                </div>
            </div>
            
            <div style="text-align: center; margin-top: 20px; font-size: 12px; color: #6c757d;">
                <p>&copy; ' . date('Y') . ' Go Puppy Go. All rights reserved.</p>
                <p>This is an automated message, please do not reply to this email.</p>
            </div>
        </body>
        </html>';
    }

    private function sendEmail($to, $subject, $htmlMessage) {
        try {
            // Create PHPMailer instance
            $mail = new PHPMailer(true);
            
            // Server settings
            $mail->isSMTP();
            $mail->Host = $this->smtpHost;
            $mail->SMTPAuth = true;
            $mail->Username = $this->username;
            $mail->Password = $this->password;
            $mail->SMTPSecure = $this->encryption === 'tls' ? PHPMailer::ENCRYPTION_STARTTLS : PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = $this->smtpPort;
            
            // For development debugging (disabled for now)
            // $mail->SMTPDebug = SMTP::DEBUG_SERVER;
            
            // Recipients
            $mail->setFrom($this->fromAddress, $this->fromName);
            $mail->addAddress($to);
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $htmlMessage;
            
            // Create plain text version
            $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $htmlMessage));
            
            // Send the email
            $result = $mail->send();
            
            // Log successful send
            if ($result) {
                error_log("✅ Email sent successfully to: " . $to);
            }
            
            return $result;
            
        } catch (Exception $e) {
            error_log("❌ Email send failed to " . $to . ": " . $e->getMessage());
            error_log("❌ Email configuration - Host: " . $this->smtpHost . ", Port: " . $this->smtpPort . ", User: " . $this->username);
            
            // In case of failure, also log email for debugging
            $this->logEmail($to, $subject, $htmlMessage);
            
            return false;
        }
    }

    public function isDevEnvironment() {
        // Return false to always try sending real emails
        // Change this to true if you want to disable email sending for testing
        return false;
    }

    private function logEmail($to, $subject, $message) {
        $logDir = __DIR__ . '/../../logs/emails';
        
        // Create logs directory if it doesn't exist
        if (!file_exists($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        // Create a unique filename for each email
        $filename = date('Y-m-d_H-i-s') . '_' . preg_replace('/[^a-zA-Z0-9]/', '_', $to) . '.html';
        $emailFile = $logDir . '/' . $filename;
        
        // Create the full HTML email content
        $htmlContent = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>' . htmlspecialchars($subject) . '</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .email-container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); max-width: 600px; margin: 0 auto; }
        .header { background: #4472c4; color: white; padding: 15px; margin: -20px -20px 20px -20px; border-radius: 8px 8px 0 0; }
        .email-info { background: #e3f2fd; padding: 10px; border-radius: 5px; margin-bottom: 20px; font-size: 14px; }
        .content { line-height: 1.6; }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1 style="margin: 0;">' . htmlspecialchars($subject) . '</h1>
        </div>
        <div class="email-info">
            <strong>📧 EMAIL LOG (Development Mode)</strong><br>
            <strong>To:</strong> ' . htmlspecialchars($to) . '<br>
            <strong>Subject:</strong> ' . htmlspecialchars($subject) . '<br>
            <strong>Timestamp:</strong> ' . date('Y-m-d H:i:s') . '<br>
            <strong>Note:</strong> This email was logged instead of being sent (development mode)
        </div>
        <div class="content">
            ' . $message . '
        </div>
    </div>
</body>
</html>';

        // Save the email to file
        file_put_contents($emailFile, $htmlContent);
        
        // Also log to main emails.log for reference
        $logFile = $logDir . '/../emails.log';
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'to' => $to,
            'subject' => $subject,
            'file_saved' => $filename,
            'separator' => str_repeat('=', 80)
        ];
        
        $logContent = sprintf(
            "[%s] Email to: %s\nSubject: %s\n%s\n%s\n\n",
            $logEntry['timestamp'],
            $logEntry['to'],
            $logEntry['subject'],
            $logEntry['separator'],
            $logEntry['message']
        );
        
        file_put_contents($logFile, $logContent, FILE_APPEND | LOCK_EX);
        return true; // Simulate successful sending in development
    }

    private function getBaseUrl() {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $port = $_SERVER['SERVER_PORT'] ?? '80';
        
        $baseUrl = $protocol . '://' . $host;
        if (($protocol === 'http' && $port !== '80') || ($protocol === 'https' && $port !== '443')) {
            $baseUrl .= ':' . $port;
        }
        
        // Get the directory path
        $scriptPath = dirname($_SERVER['SCRIPT_NAME'] ?? '');
        if ($scriptPath !== '/') {
            $baseUrl .= $scriptPath;
        }
        
        return $baseUrl;
    }

    public function generateVerificationToken() {
        return bin2hex(random_bytes(32));
    }

    public function generateAdminAccessCode() {
        // Generate a 8-character alphanumeric code
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $code = '';
        for ($i = 0; $i < 8; $i++) {
            $code .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $code;
    }
}