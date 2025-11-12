<?php
// verify.php is in public/
// bootstrap.php is in project root
require_once __DIR__ . '/../bootstrap.php';

use Angel\IapGroupProject\Controllers\AuthController;

$auth = new AuthController();

// Redirect if already logged in
if ($auth->isLoggedIn()) {
    header("Location: dashboard.php");
    exit;
}

$message = '';
$messageType = ''; // 'success', 'error', 'warning'
$verified = false;

// Check if token is provided
if (isset($_GET['token']) && !empty($_GET['token'])) {
    $token = trim($_GET['token']);
    
    try {
        // Get database configuration from environment
        $db_host = $_ENV['DB_HOST'] ?? 'localhost';
        $db_port = $_ENV['DB_PORT'] ?? '3307';
        $db_name = $_ENV['DB_NAME'] ?? 'gopuppygo';
        $db_user = $_ENV['DB_USER'] ?? 'root';
        $db_pass = $_ENV['DB_PASS'] ?? '';
        $db_charset = $_ENV['DB_CHARSET'] ?? 'utf8mb4';

        // Create PDO connection
        $dsn = "mysql:host={$db_host};port={$db_port};dbname={$db_name};charset={$db_charset}";
        $pdo = new PDO($dsn, $db_user, $db_pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);

        // Check if token exists and is valid
        $stmt = $pdo->prepare('
            SELECT evt.token_id, evt.user_id, evt.expires_at, evt.used, u.email, u.full_name, u.verified
            FROM email_verification_tokens evt
            JOIN users u ON evt.user_id = u.user_id
            WHERE evt.token = ? AND evt.used = FALSE
        ');
        $stmt->execute([$token]);
        $tokenData = $stmt->fetch();

        if (!$tokenData) {
            $messageType = 'error';
            $message = 'Invalid or already used verification token. Please request a new verification email.';
        } elseif (strtotime($tokenData['expires_at']) < time()) {
            $messageType = 'error';
            $message = 'This verification link has expired. Please request a new verification email.';
        } elseif ($tokenData['verified']) {
            $messageType = 'warning';
            $message = 'Your email has already been verified. You can now log in to your account.';
            $verified = true;
        } else {
            // Begin transaction
            $pdo->beginTransaction();

            try {
                // Mark user as verified
                $stmt = $pdo->prepare('UPDATE users SET verified = TRUE WHERE user_id = ?');
                $stmt->execute([$tokenData['user_id']]);

                // Mark token as used
                $stmt = $pdo->prepare('UPDATE email_verification_tokens SET used = TRUE WHERE token_id = ?');
                $stmt->execute([$tokenData['token_id']]);

                // Commit transaction
                $pdo->commit();

                $messageType = 'success';
                $message = 'Email verified successfully! You can now log in to your account.';
                $verified = true;

            } catch (Exception $e) {
                // Rollback on error
                $pdo->rollBack();
                throw $e;
            }
        }

    } catch (PDOException $e) {
        $messageType = 'error';
        $message = 'Database error occurred. Please try again later.';
        error_log("Verification Error: " . $e->getMessage());
    } catch (Exception $e) {
        $messageType = 'error';
        $message = 'An error occurred during verification. Please try again later.';
        error_log("Verification Error: " . $e->getMessage());
    }

} else {
    $messageType = 'error';
    $message = 'No verification token provided. Please check your email for the verification link.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" href="/IAP-GROUP-PROJECT/public/images/gopuppygo-logo.svg" type="image/svg+xml">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification - Go Puppy Go</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            max-width: 600px;
            width: 100%;
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            animation: slideUp 0.5s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .header {
            text-align: center;
            padding: 50px 30px 30px;
            background: white;
        }

        .logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            margin-bottom: 30px;
        }

        .logo-icon {
            width: 60px;
            height: 60px;
            background: #3b4a6b;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 32px;
            animation: bounce 1s ease-in-out;
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .logo-text {
            font-size: 36px;
            font-weight: 600;
            color: #3b4a6b;
        }

        .content {
            padding: 0 40px 50px;
            text-align: center;
        }

        .icon-container {
            width: 120px;
            height: 120px;
            margin: 0 auto 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 64px;
            animation: scaleIn 0.5s ease-out 0.2s both;
        }

        @keyframes scaleIn {
            from {
                opacity: 0;
                transform: scale(0.5);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .icon-container.success {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            border: 3px solid #28a745;
        }

        .icon-container.error {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            border: 3px solid #dc3545;
        }

        .icon-container.warning {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeeba 100%);
            border: 3px solid #ffc107;
        }

        h1 {
            font-size: 32px;
            color: #333;
            margin-bottom: 20px;
            font-weight: 600;
        }

        .message {
            font-size: 18px;
            line-height: 1.6;
            color: #666;
            margin-bottom: 40px;
        }

        .message.success {
            color: #155724;
        }

        .message.error {
            color: #721c24;
        }

        .message.warning {
            color: #856404;
        }

        .button-group {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-block;
            padding: 16px 32px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
            cursor: pointer;
            border: none;
        }

        .btn-primary {
            background: #4472c4;
            color: white;
        }

        .btn-primary:hover {
            background: #365a96;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(68, 114, 196, 0.4);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(108, 117, 125, 0.4);
        }

        .info-box {
            background: #f8f9fa;
            border-left: 4px solid #4472c4;
            padding: 20px;
            margin: 30px 0;
            border-radius: 4px;
            text-align: left;
        }

        .info-box h3 {
            margin: 0 0 10px 0;
            color: #3b4a6b;
            font-size: 18px;
        }

        .info-box ul {
            margin: 10px 0 0 20px;
            color: #666;
        }

        .info-box li {
            margin: 8px 0;
            line-height: 1.5;
        }

        @media (max-width: 768px) {
            .container {
                margin: 10px;
            }
            
            .content {
                padding: 0 20px 40px;
            }
            
            h1 {
                font-size: 24px;
            }
            
            .message {
                font-size: 16px;
            }
            
            .button-group {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
        }

        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 14px;
            color: #666;
            border-top: 1px solid #e0e0e0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">
                <div class="logo-icon">🐶</div>
                <div class="logo-text">GoPuppyGo</div>
            </div>
        </div>

        <div class="content">
            <?php if ($messageType === 'success'): ?>
                <div class="icon-container success">
                    ✓
                </div>
                <h1>Email Verified!</h1>
                <p class="message success">
                    <?php echo htmlspecialchars($message); ?>
                </p>
                
                <div class="info-box">
                    <h3>🎉 What's Next?</h3>
                    <ul>
                        <li>Log in to your account using your credentials</li>
                        <li>Complete your profile to get personalized recommendations</li>
                        <li>Start browsing available dogs for adoption</li>
                        <li>Save your favorite dogs and contact rehomers</li>
                    </ul>
                </div>

                <div class="button-group">
                    <a href="login.php" class="btn btn-primary">
                        🔐 Go to Login
                    </a>
                    <a href="index.php" class="btn btn-secondary">
                        🏠 Back to Home
                    </a>
                </div>

            <?php elseif ($messageType === 'warning'): ?>
                <div class="icon-container warning">
                    ⚠️
                </div>
                <h1>Already Verified</h1>
                <p class="message warning">
                    <?php echo htmlspecialchars($message); ?>
                </p>

                <div class="button-group">
                    <a href="login.php" class="btn btn-primary">
                        🔐 Go to Login
                    </a>
                    <a href="index.php" class="btn btn-secondary">
                        🏠 Back to Home
                    </a>
                </div>

            <?php else: ?>
                <div class="icon-container error">
                    ✕
                </div>
                <h1>Verification Failed</h1>
                <p class="message error">
                    <?php echo htmlspecialchars($message); ?>
                </p>

                <div class="info-box">
                    <h3>💡 Need Help?</h3>
                    <ul>
                        <li>Make sure you clicked the complete link from your email</li>
                        <li>Check if the link has expired (valid for 24 hours)</li>
                        <li>Try registering again if the link is too old</li>
                        <li>Contact support if you continue having issues</li>
                    </ul>
                </div>

                <div class="button-group">
                    <a href="register.php" class="btn btn-primary">
                        ➕ Register Again
                    </a>
                    <a href="login.php" class="btn btn-secondary">
                        🔐 Try Login
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <div class="footer">
            <p><strong>Go Puppy Go Team</strong></p>
            <p>Connecting loving homes with dogs in need 🐕❤️</p>
        </div>
    </div>

    <?php if ($verified): ?>
    <script>
        // Optional: Auto-redirect to login after 5 seconds on successful verification
        setTimeout(function() {
            window.location.href = 'login.php';
        }, 5000);
    </script>
    <?php endif; ?>
</body>
</html>