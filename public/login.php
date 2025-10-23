<?php
// login.php
require_once __DIR__ . '/../bootstrap.php';

use Angel\IapGroupProject\Controllers\AuthController;

$auth = new AuthController();

// Redirect if already logged in
if ($auth->isLoggedIn()) {
    $user = $auth->getCurrentUser();
    $defaultRedirect = 'client/client-dashboard.php';
    
    if ($user && $user->getRoleName()) {
        switch ($user->getRoleName()) {
            case 'admin':
                $defaultRedirect = 'admin/admin-dashboard.php';
                break;
            case 'rehomer':
                $defaultRedirect = 'rehomer/rehomer-dashboard.php';
                break;
            case 'client':
            default:
                $defaultRedirect = 'client/client-dashboard.php';
                break;
        }
    }
    
    $redirectUrl = $_SESSION['redirect_after_login'] ?? $defaultRedirect;
    unset($_SESSION['redirect_after_login']);
    header("Location: $redirectUrl");
    exit;
}

$errors = [];
$messages = [];

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['login'])) {
        $identifier = trim($_POST['identifier'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if ($auth->login($identifier, $password)) {
            // Successful login - redirect based on user role
            $user = $auth->getCurrentUser();
            $defaultRedirect = 'client/client-dashboard.php'; // Default for clients
            
            // Debug: Log login success
            error_log("LOGIN SUCCESS: User " . $identifier . " logged in successfully");
            error_log("User role: " . ($user ? $user->getRoleName() : 'NO USER'));
            
            if ($user && $user->getRoleName()) {
                switch ($user->getRoleName()) {
                    case 'admin':
                        $defaultRedirect = 'admin/admin-dashboard.php';
                        break;
                    case 'rehomer':
                        $defaultRedirect = 'rehomer/rehomer-dashboard.php';
                        break;
                    case 'client':
                    default:
                        $defaultRedirect = 'client/client-dashboard.php';
                        break;
                }
            }
            
            $redirectUrl = $_SESSION['redirect_after_login'] ?? $defaultRedirect;
            unset($_SESSION['redirect_after_login']);
            
            // Debug: Log redirect
            error_log("REDIRECTING TO: " . $redirectUrl);
            
            header("Location: $redirectUrl");
            exit;
        } else {
            $errors = $auth->getErrors();
        }
    }
}

// Get any flash messages
if (isset($_SESSION['flash_messages'])) {
    $messages = $_SESSION['flash_messages'];
    unset($_SESSION['flash_messages']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Go Puppy Go</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            padding: 40px;
            text-align: center;
        }

        .logo {
            margin-bottom: 30px;
        }

        .logo h1 {
            color: #333;
            font-size: 2.5em;
            margin-bottom: 10px;
        }

        .logo p {
            color: #666;
            font-size: 1.1em;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #333;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e1e1;
            border-radius: 10px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        .btn:active {
            transform: translateY(0);
        }

        .forgot-password {
            margin: 20px 0;
            text-align: center;
        }

        .forgot-password a {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
        }

        .forgot-password a:hover {
            text-decoration: underline;
        }

        .register-link {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e1e1e1;
            text-align: center;
        }

        .register-link p {
            color: #666;
            margin-bottom: 10px;
        }

        .register-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }

        .register-link a:hover {
            text-decoration: underline;
        }

        .alert {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: left;
        }

        .alert-danger {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }

        .alert-success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }

        .error-list {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .error-list li {
            margin-bottom: 5px;
        }

        .error-list li:before {
            content: "• ";
            color: #dc3545;
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 30px 20px;
            }
            
            .logo h1 {
                font-size: 2em;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <h1>🐶 Go Puppy Go</h1>
            <p>Welcome back!</p>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="error-list">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if (!empty($messages)): ?>
            <div class="alert alert-success">
                <ul class="error-list">
                    <?php foreach ($messages as $message): ?>
                        <li><?php echo htmlspecialchars($message); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="identifier">Email or Username</label>
                <input 
                    type="text" 
                    id="identifier" 
                    name="identifier" 
                    placeholder="Enter your email or username"
                    value="<?php echo htmlspecialchars($_POST['identifier'] ?? ''); ?>"
                    required
                    autocomplete="username"
                >
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    placeholder="Enter your password"
                    required
                    autocomplete="current-password"
                >
            </div>

            <button type="submit" name="login" class="btn">
                Login
            </button>
        </form>

        <div class="forgot-password">
            <a href="forgot-password.php">Forgot your password?</a>
        </div>

        <div class="register-link">
            <p>Don't have an account?</p>
            <a href="register.php">Create Account</a>
        </div>
    </div>

    <script>
        // Auto-focus on the first input field
        document.getElementById('identifier').focus();

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const identifier = document.getElementById('identifier').value.trim();
            const password = document.getElementById('password').value;

            if (!identifier) {
                e.preventDefault();
                alert('Please enter your email or username.');
                document.getElementById('identifier').focus();
                return;
            }

            if (identifier.length < 3) {
                e.preventDefault();
                alert('Email or username must be at least 3 characters long.');
                document.getElementById('identifier').focus();
                return;
            }

            if (!password) {
                e.preventDefault();
                alert('Please enter your password.');
                document.getElementById('password').focus();
                return;
            }

            if (password.length < 6) {
                e.preventDefault();
                alert('Password must be at least 6 characters long.');
                document.getElementById('password').focus();
                return;
            }

            // Show loading state
            const submitBtn = document.querySelector('button[name="login"]');
            submitBtn.textContent = 'Logging in...';
            submitBtn.disabled = true;
        });

        // Clear any previous form errors when user starts typing
        document.querySelectorAll('input').forEach(input => {
            input.addEventListener('input', function() {
                const alerts = document.querySelectorAll('.alert-danger');
                alerts.forEach(alert => alert.style.display = 'none');
            });
        });
    </script>
</body>
</html>