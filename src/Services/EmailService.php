<?php

namespace Angel\IapGroupProject\Services;

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
    }

    public function sendVerificationEmail($email, $fullName, $verificationToken, $isAdmin = false, $adminAccessCode = null) {
        $subject = "Verify Your Go Puppy Go Account";
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
            <title>Verify Your Email - Go Puppy Go</title>
        </head>
        <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
            <div style="background-color: #4472c4; color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0;">
                <h1 style="margin: 0; font-size: 28px;">🐶 Go Puppy Go</h1>
                <p style="margin: 10px 0 0 0; font-size: 16px;">Welcome to our community!</p>
            </div>
            
            <div style="background-color: #f8f9fa; padding: 30px; border-radius: 0 0 8px 8px; border: 1px solid #dee2e6;">
                <h2 style="color: #4472c4; margin: 0 0 20px 0;">Hello ' . htmlspecialchars($fullName) . '!</h2>
                
                <p>Thank you for registering with Go Puppy Go! To complete your account setup, please verify your email address by clicking the button below:</p>
                
                <div style="text-align: center; margin: 30px 0;">
                    <a href="' . htmlspecialchars($verificationLink) . '" 
                       style="background-color: #28a745; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;">
                        ✅ Verify Email Address
                    </a>
                </div>
                
                ' . $adminSection . '
                
                <div style="background-color: #e9ecef; padding: 15px; border-radius: 4px; margin: 20px 0;">
                    <p style="margin: 0; font-size: 14px; color: #495057;">
                        <strong>Alternative verification:</strong> If the button above doesn\'t work, copy and paste this link into your browser:
                    </p>
                    <p style="margin: 5px 0 0 0; word-break: break-all; font-size: 12px; color: #6c757d;">
                        ' . htmlspecialchars($verificationLink) . '
                    </p>
                </div>
                
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
        // Convert HTML to plain text for fallback
        $plainMessage = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $htmlMessage));
        
        // Headers
        $headers = [
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $this->fromName . ' <' . $this->fromAddress . '>',
            'Reply-To: ' . $this->fromAddress,
            'X-Mailer: PHP/' . phpversion()
        ];
        
        // For development/testing - log email instead of sending
        if ($this->isDevEnvironment()) {
            return $this->logEmail($to, $subject, $htmlMessage);
        }
        
        // Send email using PHP's mail function
        // Note: In production, you'd want to use a proper email library like PHPMailer or SwiftMailer
        return mail($to, $subject, $htmlMessage, implode("\r\n", $headers));
    }

    private function isDevEnvironment() {
        // Check if we're in development (no proper SMTP configured)
        return empty($this->username) || $this->smtpHost === 'localhost';
    }

    private function logEmail($to, $subject, $message) {
        $logFile = __DIR__ . '/../../logs/emails.log';
        $logDir = dirname($logFile);
        
        // Create logs directory if it doesn't exist
        if (!file_exists($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'to' => $to,
            'subject' => $subject,
            'message' => $message,
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