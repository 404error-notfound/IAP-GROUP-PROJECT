<?php
require_once __DIR__ . '/../bootstrap.php';

use Angel\IapGroupProject\Repositories\UserRepository;

$userRepository = new UserRepository();
$message = '';
$success = false;

if (isset($_GET['token'])) {
    $token = trim($_GET['token']);
    
    if (empty($token)) {
        $message = 'Invalid verification token.';
    } else {
        $userId = $userRepository->verifyEmailToken($token);
        
        if ($userId) {
            $success = true;
            $message = 'Email verified successfully! You can now log in to your account.';
        } else {
            $message = 'Invalid or expired verification token. Please request a new verification email or contact support.';
        }
    }
} else {
    $message = 'No verification token provided.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
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
            max-width: 500px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            padding: 0;
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, <?php echo $success ? '#28a745' : '#dc3545'; ?> 0%, <?php echo $success ? '#20c997' : '#fd7e14'; ?> 100%);
            color: white;
            text-align: center;
            padding: 40px 20px;
        }

        .icon {
            font-size: 60px;
            margin-bottom: 20px;
            display: block;
        }

        .header h1 {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .header p {
            font-size: 16px;
            opacity: 0.9;
        }

        .content {
            padding: 40px 30px;
            text-align: center;
        }

        .message {
            font-size: 18px;
            line-height: 1.6;
            color: #333;
            margin-bottom: 30px;
        }

        .actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .btn-primary {
            background: #4472c4;
            color: white;
        }

        .btn-primary:hover {
            background: #365a96;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: #f8f9fa;
            color: #495057;
            border: 1px solid #dee2e6;
        }

        .btn-secondary:hover {
            background: #e9ecef;
            transform: translateY(-2px);
        }

        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 14px;
            color: #6c757d;
            border-top: 1px solid #dee2e6;
        }

        @media (max-width: 480px) {
            .container {
                margin: 10px;
                border-radius: 12px;
            }
            
            .header {
                padding: 30px 15px;
            }
            
            .content {
                padding: 30px 20px;
            }
            
            .actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <span class="icon"><?php echo $success ? '✅' : '❌'; ?></span>
            <h1><?php echo $success ? 'Email Verified!' : 'Verification Failed'; ?></h1>
            <p><?php echo $success ? 'Your account is now active' : 'Something went wrong'; ?></p>
        </div>
        
        <div class="content">
            <div class="message">
                <?php echo htmlspecialchars($message); ?>
            </div>
            
            <div class="actions">
                <?php if ($success): ?>
                    <a href="login.php" class="btn btn-primary">
                        🔐 Go to Login
                    </a>
                    <a href="index.php" class="btn btn-secondary">
                        🏠 Home
                    </a>
                <?php else: ?>
                    <a href="register.php" class="btn btn-primary">
                        📝 Register Again
                    </a>
                    <a href="index.php" class="btn btn-secondary">
                        🏠 Home
                    </a>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="footer">
            <p>&copy; <?php echo date('Y'); ?> Go Puppy Go. All rights reserved.</p>
            <p>If you continue to have issues, please contact our support team.</p>
        </div>
    </div>
</body>
</html>