<?php
require_once __DIR__ . '/bootstrap.php';

// Test email configuration
echo "<h1>Email Configuration Test</h1>";
echo "<h2>Environment Variables:</h2>";
echo "<ul>";
echo "<li>MAIL_HOST: " . ($_ENV['MAIL_HOST'] ?? 'NOT SET') . "</li>";
echo "<li>MAIL_PORT: " . ($_ENV['MAIL_PORT'] ?? 'NOT SET') . "</li>";
echo "<li>MAIL_USERNAME: " . ($_ENV['MAIL_USERNAME'] ?? 'NOT SET') . "</li>";
echo "<li>MAIL_PASSWORD: " . (isset($_ENV['MAIL_PASSWORD']) ? '***SET*** (' . strlen($_ENV['MAIL_PASSWORD']) . ' chars)' : 'NOT SET') . "</li>";
echo "<li>MAIL_ENCRYPTION: " . ($_ENV['MAIL_ENCRYPTION'] ?? 'NOT SET') . "</li>";
echo "<li>MAIL_FROM_ADDRESS: " . ($_ENV['MAIL_FROM_ADDRESS'] ?? 'NOT SET') . "</li>";
echo "<li>MAIL_FROM_NAME: " . ($_ENV['MAIL_FROM_NAME'] ?? 'NOT SET') . "</li>";
echo "</ul>";

// Test PHPMailer directly
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

echo "<h2>Testing PHPMailer Directly:</h2>";
try {
    $mail = new PHPMailer(true);
    
    // Server settings
    $mail->isSMTP();
    $mail->Host = $_ENV['MAIL_HOST'];
    $mail->SMTPAuth = true;
    $mail->Username = $_ENV['MAIL_USERNAME'];
    $mail->Password = $_ENV['MAIL_PASSWORD'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = $_ENV['MAIL_PORT'];
    
    // Recipients
    $mail->setFrom($_ENV['MAIL_FROM_ADDRESS'], $_ENV['MAIL_FROM_NAME']);
    $mail->addAddress('angela.omondi@strathmore.edu', 'Test User'); // Change this to your test email
    
    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Test Email from Go Puppy Go';
    $mail->Body = '<h1>Test Email</h1><p>This is a test email to verify SMTP configuration.</p>';
    
    $result = $mail->send();
    echo "<p style='color: green;'>✅ Direct PHPMailer test: SUCCESS</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Direct PHPMailer test failed: " . $e->getMessage() . "</p>";
}

// Test email service
use Angel\IapGroupProject\Services\EmailService;

echo "<h2>Testing Email Service:</h2>";
try {
    $emailService = new EmailService();
    $testResult = $emailService->sendVerificationEmail(
        'angela.omondi@strathmore.edu', // Change this to your test email
        'Test User', 
        'test_token_123',
        false,
        null
    );
    
    echo "<p style='color: " . ($testResult ? 'green' : 'red') . ";'>Email Service test result: " . ($testResult ? '✅ SUCCESS' : '❌ FAILED') . "</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Email Service Error: " . $e->getMessage() . "</p>";
}

echo "<h2>PHP Configuration:</h2>";
echo "<ul>";
echo "<li>mail() function available: " . (function_exists('mail') ? 'YES' : 'NO') . "</li>";
echo "<li>OpenSSL extension loaded: " . (extension_loaded('openssl') ? 'YES' : 'NO') . "</li>";
echo "<li>Socket extension loaded: " . (extension_loaded('sockets') ? 'YES' : 'NO') . "</li>";
echo "</ul>";
?>