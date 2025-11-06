<?php
// login.php
require_once __DIR__ . '/../bootstrap.php';

$errors = [];
$messages = [];

// Debug: Log all POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("LOGIN DEBUG: POST request received, POST keys: " . implode(', ', array_keys($_POST)));
}

// Handle login form submission - check for identifier field instead of login button
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['identifier'])) {
    error_log("LOGIN DEBUG: Entering login handler");
    $identifier = trim($_POST['identifier'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($identifier && $password) {
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

            // Check if user exists by email or full_name
            $stmt = $pdo->prepare('
                SELECT u.user_id, u.full_name, u.email, u.password_hash, u.verified, 
                       u.role_id, u.gender_id, u.created_at,
                       ur.role_name,
                       ug.gender_name
                FROM users u
                JOIN user_roles ur ON u.role_id = ur.role_id
                LEFT JOIN user_gender ug ON u.gender_id = ug.gender_id
                WHERE u.email = ? OR u.full_name = ?
            ');
            $stmt->execute([$identifier, $identifier]);
            $user = $stmt->fetch();

            error_log("LOGIN: User lookup for identifier '$identifier' - " . ($user ? "FOUND" : "NOT FOUND"));

            // Check user exists and password is correct
            if ($user && password_verify($password, $user['password_hash'])) {
                error_log("LOGIN: Password verified for user_id={$user['user_id']}, verified={$user['verified']}, role={$user['role_name']}");
                
                // Check if email is verified
                if (!$user['verified']) {
                    error_log("LOGIN: User not verified, blocking login");
                    $errors[] = 'Please verify your email address before logging in. Check your inbox for the verification link.';
                } else {
                    error_log("LOGIN: User verified, creating session");
                    
                    // Login successful - set session in the format AuthController expects
                    $_SESSION['user'] = [
                        'user_id' => $user['user_id'],
                        'full_name' => $user['full_name'],
                        'email' => $user['email'],
                        'role_name' => $user['role_name'],
                        'role_id' => $user['role_id'],
                        'gender_id' => $user['gender_id'],
                        'gender_name' => $user['gender_name'] ?? null,
                        'verified' => $user['verified'],
                        'created_at' => $user['created_at']
                    ];
                    $_SESSION['login_time'] = time();
                    $_SESSION['last_activity'] = time();

                    error_log("LOGIN: Session created - user_id={$_SESSION['user']['user_id']}, role={$_SESSION['user']['role_name']}");

                    // Determine redirect based on role
                    $redirectUrl = 'client/client-dashboard.php'; // Default

                    switch ($user['role_name']) {
                        case 'admin':
                            $redirectUrl = 'admin/admin-dashboard.php';
                            break;
                        case 'rehomer':
                            $redirectUrl = 'rehomer/rehomer-dashboard.php';
                            break;
                        case 'client':
                        default:
                            $redirectUrl = 'client/client-dashboard.php';
                            break;
                    }

                    error_log("LOGIN: Redirecting to $redirectUrl");
                    
                    // Check if headers were already sent
                    if (headers_sent($file, $line)) {
                        error_log("LOGIN ERROR: Headers already sent in $file on line $line - cannot redirect!");
                        error_log("LOGIN: Using JavaScript fallback redirect");
                        echo "<script>window.location.href = '{$redirectUrl}';</script>";
                        echo "<noscript><meta http-equiv='refresh' content='0;url={$redirectUrl}'></noscript>";
                        exit;
                    }
                    
                    // Redirect to dashboard
                    header("Location: {$redirectUrl}");
                    exit;
                }
            } else {
                error_log("LOGIN: Authentication failed for identifier '$identifier'");
                $errors[] = 'Invalid email/username or password.';
            }

        } catch (PDOException $e) {
            error_log("Login DB Error: " . $e->getMessage());
            $errors[] = 'Database error. Please try again later.';
        }
    } else {
        $errors[] = 'All fields are required.';
    }
}

// Get any flash messages from session
if (isset($_SESSION['flash_messages'])) {
    $messages = $_SESSION['flash_messages'];
    unset($_SESSION['flash_messages']);
}

// Check for verification success message
if (isset($_GET['verified']) && $_GET['verified'] == '1') {
    $messages[] = 'Your email has been verified successfully! You can now log in.';
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

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
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

        .error-list, .success-list {
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

        .success-list li {
            margin-bottom: 5px;
        }

        .success-list li:before {
            content: "• ";
            color: #28a745;
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
                <ul class="success-list">
                    <?php foreach ($messages as $message): ?>
                        <li><?php echo htmlspecialchars($message); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="identifier">Email or Username</label>
                <input type="text" id="identifier" name="identifier" placeholder="Enter your email or username"
                    value="<?php echo htmlspecialchars($_POST['identifier'] ?? ''); ?>" required
                    autocomplete="username">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required
                    autocomplete="current-password">
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
        document.querySelector('form').addEventListener('submit', function (e) {
            const identifier = document.getElementById('identifier').value.trim();
            const password = document.getElementById('password').value;

            if (!identifier) {
                e.preventDefault();
                alert('Please enter your email or username.');
                document.getElementById('identifier').focus();
                return;
            }

            if (!password) {
                e.preventDefault();
                alert('Please enter your password.');
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
            input.addEventListener('input', function () {
                const alerts = document.querySelectorAll('.alert-danger');
                alerts.forEach(alert => alert.style.display = 'none');
            });
        });
    </script>
</body>

</html>