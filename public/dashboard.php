<?php
// dashboard.php
require_once __DIR__ . '/../bootstrap.php';

use Angel\IapGroupProject\Controllers\AuthController;

$auth = new AuthController();

// Require user to be logged in
$auth->requireLogin();

// Check session timeout
if (!$auth->checkSessionTimeout()) {
    header("Location: /login.php");
    exit;
}

$user = $auth->getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Go Puppy Go</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            min-height: 100vh;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.8em;
            font-weight: bold;
        }

        .nav-menu {
            display: flex;
            list-style: none;
            gap: 30px;
        }

        .nav-menu a {
            color: white;
            text-decoration: none;
            transition: opacity 0.3s;
        }

        .nav-menu a:hover {
            opacity: 0.8;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #667eea;
            font-weight: bold;
        }

        .logout-btn {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .logout-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 20px;
        }

        .welcome-section {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }

        .welcome-title {
            font-size: 2.5em;
            color: #333;
            margin-bottom: 10px;
        }

        .welcome-subtitle {
            color: #666;
            font-size: 1.2em;
            margin-bottom: 20px;
        }

        .user-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .stat-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }

        .stat-number {
            font-size: 2em;
            font-weight: bold;
            color: #667eea;
        }

        .stat-label {
            color: #666;
            margin-top: 5px;
        }

        .actions-section {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }

        .actions-title {
            font-size: 1.8em;
            color: #333;
            margin-bottom: 20px;
        }

        .action-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .action-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 15px;
            text-decoration: none;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }

        .action-card h3 {
            font-size: 1.4em;
            margin-bottom: 10px;
        }

        .action-card p {
            opacity: 0.9;
            line-height: 1.5;
        }

        .recent-activity {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-top: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }

        .activity-title {
            font-size: 1.8em;
            color: #333;
            margin-bottom: 20px;
        }

        .activity-item {
            display: flex;
            align-items: center;
            padding: 15px;
            border-left: 4px solid #667eea;
            background: #f8f9fa;
            margin-bottom: 15px;
            border-radius: 5px;
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            background: #667eea;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
        }

        .activity-content {
            flex: 1;
        }

        .activity-text {
            color: #333;
            margin-bottom: 5px;
        }

        .activity-time {
            color: #666;
            font-size: 0.9em;
        }

        @media (max-width: 768px) {
            .nav-container {
                flex-direction: column;
                gap: 20px;
            }

            .nav-menu {
                gap: 20px;
            }

            .welcome-title {
                font-size: 2em;
            }

            .action-cards {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="nav-container">
            <div class="logo">🐶 Go Puppy Go</div>
            <nav>
                <ul class="nav-menu">
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="browse-puppies.php">Browse Puppies</a></li>
                    <?php if ($user->isOwner()): ?>
                        <li><a href="add-puppy.php">Add Puppy</a></li>
                        <li><a href="my-puppies.php">My Puppies</a></li>
                    <?php endif; ?>
                    <?php if ($user->isAdmin()): ?>
                        <li><a href="admin.php">Admin Panel</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
            <div class="user-menu">
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($user->getUsername(), 0, 1)); ?>
                    </div>
                    <span><?php echo htmlspecialchars($user->getUsername()); ?></span>
                </div>
                <form method="POST" action="logout.php" style="display: inline;">
                    <button type="submit" class="logout-btn">Logout</button>
                </form>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="welcome-section">
            <h1 class="welcome-title">Welcome back, <?php echo htmlspecialchars($user->getUsername()); ?>! 👋</h1>
            <p class="welcome-subtitle">Ready to help some adorable puppies find their forever homes?</p>
            
            <div class="user-stats">
                <div class="stat-card">
                    <div class="stat-number">15</div>
                    <div class="stat-label">Available Puppies</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">8</div>
                    <div class="stat-label">Successful Adoptions</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">3</div>
                    <div class="stat-label">Pending Applications</div>
                </div>
                <?php if ($user->isOwner()): ?>
                <div class="stat-card">
                    <div class="stat-number">5</div>
                    <div class="stat-label">Your Puppies</div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="actions-section">
            <h2 class="actions-title">Quick Actions</h2>
            <div class="action-cards">
                <a href="browse-puppies.php" class="action-card">
                    <h3>🔍 Browse Puppies</h3>
                    <p>Find your perfect furry companion from our adorable selection of puppies looking for loving homes.</p>
                </a>

                <?php if ($user->isOwner()): ?>
                <a href="add-puppy.php" class="action-card">
                    <h3>➕ Add New Puppy</h3>
                    <p>List a new puppy for adoption and help them find their forever family.</p>
                </a>

                <a href="my-puppies.php" class="action-card">
                    <h3>🐕 Manage My Puppies</h3>
                    <p>View and manage all the puppies you've listed for adoption.</p>
                </a>
                <?php endif; ?>

                <a href="adoption-applications.php" class="action-card">
                    <h3>📋 My Applications</h3>
                    <p>Track the status of your adoption applications and messages.</p>
                </a>

                <a href="profile.php" class="action-card">
                    <h3>👤 Update Profile</h3>
                    <p>Manage your account information and preferences.</p>
                </a>

                <?php if ($user->isAdmin()): ?>
                <a href="admin.php" class="action-card">
                    <h3>⚙️ Admin Panel</h3>
                    <p>Manage users, puppies, and site settings.</p>
                </a>
                <?php endif; ?>
            </div>
        </div>

        <div class="recent-activity">
            <h2 class="activity-title">Recent Activity</h2>
            
            <div class="activity-item">
                <div class="activity-icon">🐕</div>
                <div class="activity-content">
                    <div class="activity-text">New puppy "Bella" was added to the adoption list</div>
                    <div class="activity-time">2 hours ago</div>
                </div>
            </div>

            <div class="activity-item">
                <div class="activity-icon">❤️</div>
                <div class="activity-content">
                    <div class="activity-text">Max found his forever home with the Johnson family</div>
                    <div class="activity-time">1 day ago</div>
                </div>
            </div>

            <div class="activity-item">
                <div class="activity-icon">📧</div>
                <div class="activity-content">
                    <div class="activity-text">You received a new message about Charlie</div>
                    <div class="activity-time">2 days ago</div>
                </div>
            </div>

            <div class="activity-item">
                <div class="activity-icon">🎉</div>
                <div class="activity-content">
                    <div class="activity-text">Luna was successfully adopted by the Smith family</div>
                    <div class="activity-time">3 days ago</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-refresh activity every 5 minutes
        setInterval(function() {
            // You can implement AJAX calls here to refresh activity
            console.log('Checking for new activity...');
        }, 300000);

        // Add smooth scrolling to anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    </script>
</body>
</html>