<?php
// rehomer-dashboard.php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../src/Controllers/AuthController.php';

use Angel\IapGroupProject\Controllers\AuthController;

$auth = new AuthController();

// Restrict access to logged-in users only
if (!$auth->isLoggedIn()) {
    $_SESSION['redirect_after_login'] = '../rehomer/rehomer-dashboard.php';
    header("Location: ../login.php");
    exit;
}

$user = $auth->getCurrentUser();

// Ensure only rehomers can access this page
if (!$user || strtolower($user->getRoleName()) !== 'rehomer') {
    header("Location: ../unauthorized.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rehomer Dashboard - Go Puppy Go</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #89f7fe 0%, #66a6ff 100%);
            margin: 0;
            padding: 0;
            color: #333;
        }

        header {
            background: #fff;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        header h1 {
            font-size: 1.8rem;
            color: #444;
        }

        .user-info {
            font-size: 1rem;
            color: #555;
        }

        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
        }

        .card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 20px;
        }

        .card {
            background: #fff;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }

        .card h2 {
            font-size: 1.3rem;
            margin-bottom: 10px;
            color: #444;
        }

        .card p {
            font-size: 1rem;
            color: #666;
        }

        .btn {
            display: inline-block;
            margin-top: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            padding: 10px 18px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: background 0.3s ease;
        }

        .btn:hover {
            background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%);
        }

        footer {
            margin-top: 60px;
            text-align: center;
            color: #666;
            font-size: 0.9rem;
            padding-bottom: 20px;
        }

        @media (max-width: 600px) {
            header {
                flex-direction: column;
                text-align: center;
            }

            .user-info {
                margin-top: 10px;
            }
        }
    </style>
</head>

<body>
    <header>
        <h1>🐾 Go Puppy Go - Rehomer Dashboard</h1>
        <div class="user-info">
            Welcome, <strong><?php echo htmlspecialchars($user->getUsername()); ?></strong> |
            <a href="../logout.php" style="color:#667eea; text-decoration:none; font-weight:600;">Logout</a>
        </div>
    </header>

    <div class="container">
        <h2>Hello <?php echo htmlspecialchars($user->getUsername()); ?> 👋</h2>
        <p>Here’s what’s happening in your Rehomer dashboard today.</p>

        <div class="card-grid">
            <div class="card">
                <h2>🐶 Puppies Available</h2>
                <p>View and manage all puppies currently looking for homes.</p>
                <a href="manage-puppies.php" class="btn">Manage Puppies</a>
            </div>

            <div class="card">
                <h2>🏠 Adoption Requests</h2>
                <p>Check pending adoption requests and approve or reject them.</p>
                <a href="adoption-requests.php" class="btn">View Requests</a>
            </div>

            <div class="card">
                <h2>📋 Profile Settings</h2>
                <p>Update your details, contact info, and password.</p>
                <a href="settings.php" class="btn">Edit Profile</a>
            </div>

            <div class="card">
                <h2>📞 Support</h2>
                <p>Need help? Contact admin or view FAQs.</p>
                <a href="../support.php" class="btn">Get Support</a>
            </div>
        </div>
    </div>

    <footer>
        © <?php echo date('Y'); ?> Go Puppy Go | Rehomer Portal
    </footer>
</body>

</html>