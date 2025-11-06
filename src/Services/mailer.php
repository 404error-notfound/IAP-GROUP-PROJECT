<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// mailer.php is in src/Services/
// vendor/ is in project root
// Path from src/Services/ to vendor/ is ../../vendor/
require_once __DIR__ . '/../../vendor/autoload.php';

function send_verification_email($to_email, $to_name, $verification_link) {
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = $_ENV['MAIL_HOST'] ?? 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['MAIL_USERNAME'] ?? 'angela.omondi@strathmore.edu';
        $mail->Password   = $_ENV['MAIL_PASSWORD'] ?? 'wkgondpzmphysilc';
        
        // Determine encryption and port from environment
        $mail_encryption = $_ENV['MAIL_ENCRYPTION'] ?? 'tls';
        $mail_port = $_ENV['MAIL_PORT'] ?? 587;
        
        if (strtolower($mail_encryption) === 'ssl' || $mail_port == 465) {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = 465;
        } else {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $mail_port;
        }
        
        // Sender
        $mail->setFrom(
            $_ENV['MAIL_FROM_ADDRESS'] ?? 'angela.omondi@strathmore.edu',
            $_ENV['MAIL_FROM_NAME'] ?? 'Go Puppy Go'
        );
        
        // Recipient
        $mail->addAddress($to_email, $to_name);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Welcome to Go Puppy Go! Email Verification Required';
        
        // Enhanced HTML email body
        $body = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <style>
                body {
                    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                    line-height: 1.6;
                    color: #333;
                    background-color: #f8f9fa;
                    margin: 0;
                    padding: 0;
                }
                .container {
                    max-width: 600px;
                    margin: 20px auto;
                    background: white;
                    border-radius: 12px;
                    overflow: hidden;
                    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
                }
                .header {
                    background: linear-gradient(135deg, #4472c4 0%, #3b4a6b 100%);
                    padding: 40px 20px;
                    text-align: center;
                    color: white;
                }
                .logo {
                    font-size: 48px;
                    margin-bottom: 10px;
                }
                .header h1 {
                    margin: 0;
                    font-size: 28px;
                    font-weight: 600;
                }
                .content {
                    padding: 40px 30px;
                }
                .content h2 {
                    color: #3b4a6b;
                    margin-top: 0;
                    font-size: 24px;
                }
                .content p {
                    margin: 15px 0;
                    font-size: 16px;
                }
                .button-container {
                    text-align: center;
                    margin: 30px 0;
                }
                .verify-button {
                    display: inline-block;
                    background: #4472c4;
                    color: white !important;
                    padding: 16px 40px;
                    text-decoration: none;
                    border-radius: 8px;
                    font-weight: 600;
                    font-size: 16px;
                    transition: background 0.3s ease;
                }
                .verify-button:hover {
                    background: #365a96;
                }
                .link-text {
                    background: #f8f9fa;
                    padding: 15px;
                    border-radius: 6px;
                    word-break: break-all;
                    font-size: 14px;
                    color: #666;
                    margin: 20px 0;
                }
                .footer {
                    background: #f8f9fa;
                    padding: 20px;
                    text-align: center;
                    font-size: 14px;
                    color: #666;
                    border-top: 1px solid #e0e0e0;
                }
                .footer p {
                    margin: 5px 0;
                }
                .warning {
                    background: #fff3cd;
                    border-left: 4px solid #ffc107;
                    padding: 15px;
                    margin: 20px 0;
                    border-radius: 4px;
                }
                .warning p {
                    margin: 5px 0;
                    color: #856404;
                    font-size: 14px;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <div class="logo">🐶</div>
                    <h1>Go Puppy Go</h1>
                </div>
                
                <div class="content">
                    <h2>Hello ' . htmlspecialchars($to_name) . '! 👋</h2>
                    
                    <p>Welcome to <strong>Go Puppy Go</strong> - your trusted platform for dog adoption and rehoming!</p>
                    
                    <p>Thank you for registering with us. To complete your registration and start your journey, please verify your email address by clicking the button below:</p>
                    
                    <div class="button-container">
                        <a href="' . htmlspecialchars($verification_link) . '" class="verify-button">
                            ✉️ Verify My Email
                        </a>
                    </div>
                    
                    <p style="text-align: center; color: #666; font-size: 14px;">
                        <em>Or copy and paste this link into your browser:</em>
                    </p>
                    
                    <div class="link-text">
                        ' . htmlspecialchars($verification_link) . '
                    </div>
                    
                    <div class="warning">
                        <p><strong>⚠️ Important:</strong></p>
                        <p>• This verification link will expire in <strong>24 hours</strong></p>
                        <p>• If you did not create an account, please ignore this email</p>
                        <p>• For security reasons, do not share this link with anyone</p>
                    </div>
                    
                    <p>Once verified, you\'ll be able to:</p>
                    <ul>
                        <li>Browse available dogs for adoption</li>
                        <li>Save your favorite dogs</li>
                        <li>Contact rehomers directly</li>
                        <li>Submit adoption applications</li>
                    </ul>
                    
                    <p>If you have any questions or need assistance, feel free to contact our support team.</p>
                </div>
                
                <div class="footer">
                    <p><strong>Go Puppy Go Team</strong></p>
                    <p>Connecting loving homes with dogs in need 🐕❤️</p>
                    <p style="margin-top: 15px; font-size: 12px; color: #999;">
                        © ' . date('Y') . ' Go Puppy Go. All rights reserved.
                    </p>
                </div>
            </div>
        </body>
        </html>
        ';
        
        $mail->Body = $body;
        
        // Plain text version for email clients that don't support HTML
        $mail->AltBody = "Hello {$to_name},\n\n"
            . "Welcome to Go Puppy Go!\n\n"
            . "Please verify your email address by clicking the link below:\n\n"
            . "{$verification_link}\n\n"
            . "This link will expire in 24 hours.\n\n"
            . "If you did not create an account, please ignore this email.\n\n"
            . "Best regards,\n"
            . "Go Puppy Go Team";
        
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        // Log the error for debugging
        error_log("PHPMailer Error: {$mail->ErrorInfo}");
        return false;
    }
}