<?php
// forgot-password.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" href="/IAP-GROUP-PROJECT/public/images/gopuppygo-logo.svg" type="image/svg+xml">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Go Puppy Go</title>
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

        .forgot-container {
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

        .info-box {
            background: #e3f2fd;
            border: 1px solid #bbdefb;
            color: #1565c0;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            text-align: left;
        }

        .info-box h3 {
            margin-bottom: 10px;
        }

        .info-box p {
            line-height: 1.6;
            margin-bottom: 10px;
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
            margin-bottom: 15px;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        .btn:active {
            transform: translateY(0);
        }

        .back-link {
            text-align: center;
        }

        .back-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }

        .back-link a:hover {
            text-decoration: underline;
        }

        @media (max-width: 480px) {
            .forgot-container {
                padding: 30px 20px;
            }
            
            .logo h1 {
                font-size: 2em;
            }
        }
    </style>
</head>
<body>
    <div class="forgot-container">
        <div class="logo">
            <h1>🐶 Go Puppy Go</h1>
            <p>Password Recovery</p>
        </div>

        <div class="info-box">
            <h3>🚧 Feature Coming Soon</h3>
            <p>Password reset functionality is currently under development.</p>
            <p>For now, please contact the administrator at <strong>admin@gopuppygo.com</strong> to reset your password.</p>
            <p>We apologize for any inconvenience!</p>
        </div>

        <form method="POST" action="" style="opacity: 0.5; pointer-events: none;">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    placeholder="Enter your email address"
                    disabled
                >
            </div>

            <button type="submit" class="btn" disabled>
                Send Reset Link (Coming Soon)
            </button>
        </form>

        <div class="back-link">
            <a href="login.php">← Back to Login</a>
        </div>
    </div>
</body>
</html>