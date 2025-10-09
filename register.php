<?php
// register.php
require_once 'AuthController.php';

$auth = new AuthController();

// Redirect if already logged in
if ($auth->isLoggedIn()) {
    header("Location: dashboard.php");
    exit;
}

$errors = [];
$messages = [];

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['register'])) {
        $email = trim($_POST['email'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $role = $_POST['role'] ?? 'user';
        
        if ($auth->register($email, $username, $password, $confirmPassword, $role)) {
            $_SESSION['flash_messages'] = $auth->getMessages();
            header("Location: login.php");
            exit;
        } else {
            $errors = $auth->getErrors();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Go Puppy Go</title>
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

        .register-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 450px;
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

        .form-group input, .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e1e1;
            border-radius: 10px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus, .form-group select:focus {
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

        .login-link {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e1e1e1;
            text-align: center;
        }

        .login-link p {
            color: #666;
            margin-bottom: 10px;
        }

        .login-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }

        .login-link a:hover {
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

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        @media (max-width: 480px) {
            .register-container {
                padding: 30px 20px;
            }
            
            .logo h1 {
                font-size: 2em;
            }

            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="logo">
            <h1>🐶 Go Puppy Go</h1>
            <p>Create your account</p>
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

        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    placeholder="Enter your email address"
                    value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                    required
                    autocomplete="email"
                >
            </div>

            <div class="form-group">
                <label for="username">Username</label>
                <input 
                    type="text" 
                    id="username" 
                    name="username" 
                    placeholder="Choose a username"
                    value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                    required
                    autocomplete="username"
                    minlength="3"
                >
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="password">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        placeholder="Create a password"
                        required
                        autocomplete="new-password"
                        minlength="6"
                    >
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input 
                        type="password" 
                        id="confirm_password" 
                        name="confirm_password" 
                        placeholder="Confirm your password"
                        required
                        autocomplete="new-password"
                    >
                </div>
            </div>

            <div class="form-group">
                <label for="role">Account Type</label>
                <select id="role" name="role" required>
                    <option value="user" <?php echo ($_POST['role'] ?? '') === 'user' ? 'selected' : ''; ?>>
                        Adopter - Looking for a puppy
                    </option>
                    <option value="owner" <?php echo ($_POST['role'] ?? '') === 'owner' ? 'selected' : ''; ?>>
                        Owner - I have puppies to rehome
                    </option>
                </select>
            </div>

            <button type="submit" name="register" class="btn">
                Create Account
            </button>
        </form>

        <div class="login-link">
            <p>Already have an account?</p>
            <a href="login.php">Login Here</a>
        </div>
    </div>

    <script>
        // Auto-focus on the first input field
        document.getElementById('email').focus();

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value.trim();
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            if (!email) {
                e.preventDefault();
                alert('Please enter your email address.');
                document.getElementById('email').focus();
                return;
            }

            if (!username || username.length < 3) {
                e.preventDefault();
                alert('Please enter a username (at least 3 characters).');
                document.getElementById('username').focus();
                return;
            }

            if (!password || password.length < 6) {
                e.preventDefault();
                alert('Please enter a password (at least 6 characters).');
                document.getElementById('password').focus();
                return;
            }

            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match.');
                document.getElementById('confirm_password').focus();
                return;
            }

            // Show loading state
            const submitBtn = document.querySelector('button[name="register"]');
            submitBtn.textContent = 'Creating Account...';
            submitBtn.disabled = true;
        });

        // Password confirmation validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (password !== confirmPassword && confirmPassword.length > 0) {
                this.style.borderColor = '#dc3545';
            } else {
                this.style.borderColor = '#e1e1e1';
            }
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