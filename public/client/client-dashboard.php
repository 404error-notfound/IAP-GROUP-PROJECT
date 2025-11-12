<?php
// client-dashboard.php
require_once __DIR__ . '/../../bootstrap.php';

use Angel\IapGroupProject\Controllers\AuthController;
use Angel\IapGroupProject\Database;

$auth = new AuthController();

// Require user to be logged in
$auth->requireLogin();

// Check session timeout
if (!$auth->checkSessionTimeout()) {
    header("Location: ../login.php");
    exit;
}

$user = $auth->getCurrentUser();

// Ensure user is a client
if (!$user || $user->getRoleName() !== 'client') {
    header("Location: ../login.php");
    exit;
}

// Get database connection
$db = Database::getInstance()->getConnection();

// Get client_id for this user
$stmt = $db->prepare("SELECT client_id FROM clients WHERE user_id = ?");
$stmt->execute([$user->getUserId()]);
$clientData = $stmt->fetch();

if (!$clientData) {
    header("Location: ../login.php");
    exit;
}

$clientId = $clientData['client_id'];

// Get dashboard statistics
$stats = [];

// Total Favourites
$stmt = $db->prepare("SELECT COUNT(*) as count FROM favourites WHERE client_id = ?");
$stmt->execute([$clientId]);
$stats['favourites'] = $stmt->fetchColumn();

// Total Adoption Requests
$stmt = $db->prepare("SELECT COUNT(*) as count FROM adoptions WHERE client_id = ?");
$stmt->execute([$clientId]);
$stats['adoptions'] = $stmt->fetchColumn();

// Bookings Made
$stmt = $db->prepare("SELECT COUNT(*) as count FROM bookings WHERE client_id = ?");
$stmt->execute([$clientId]);
$stats['bookings'] = $stmt->fetchColumn();

// Reviews Made
$stmt = $db->prepare("SELECT COUNT(*) as count FROM reviews WHERE client_id = ?");
$stmt->execute([$clientId]);
$stats['reviews'] = $stmt->fetchColumn();

// Get recent favourites with dog details
$stmt = $db->prepare("
    SELECT f.*, d.name, d.age, d.adoption_fee, b.breed_name, d.image_url, d.status, d.location
    FROM favourites f
    JOIN dogs d ON f.dog_id = d.dog_id
    JOIN breeds b ON d.breed_id = b.breed_id
    WHERE f.client_id = ?
    ORDER BY f.created_at DESC
    LIMIT 5
");
$stmt->execute([$clientId]);
$recentFavourites = $stmt->fetchAll();

// Get adoption requests with status
$stmt = $db->prepare("
    SELECT a.*, d.name, d.age, b.breed_name, d.image_url, d.adoption_fee, r.location as rehomer_location
    FROM adoptions a
    JOIN dogs d ON a.dog_id = d.dog_id
    JOIN breeds b ON d.breed_id = b.breed_id
    JOIN rehomers r ON d.rehomer_id = r.rehomer_id
    WHERE a.client_id = ?
    ORDER BY a.applied_at DESC
    LIMIT 5
");
$stmt->execute([$clientId]);
$adoptionRequests = $stmt->fetchAll();

// Get upcoming bookings (next 7 days)
$stmt = $db->prepare("
    SELECT bo.*, d.name, d.age, b.breed_name, d.image_url, r.location as rehomer_location, u.full_name as rehomer_name
    FROM bookings bo
    JOIN dogs d ON bo.dog_id = d.dog_id
    JOIN breeds b ON d.breed_id = b.breed_id
    JOIN rehomers r ON bo.rehomer_id = r.rehomer_id
    JOIN users u ON r.user_id = u.user_id
    WHERE bo.client_id = ? AND bo.booking_date >= NOW() AND bo.booking_date <= DATE_ADD(NOW(), INTERVAL 7 DAY)
    ORDER BY bo.booking_date ASC
");
$stmt->execute([$clientId]);
$upcomingBookings = $stmt->fetchAll();

// Get client preferences
$stmt = $db->prepare("SELECT dog_preferences FROM clients WHERE client_id = ?");
$stmt->execute([$clientId]);
$clientPrefs = $stmt->fetchColumn();
$preferences = $clientPrefs ? json_decode($clientPrefs, true) : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" href="/IAP-GROUP-PROJECT/public/images/favicon.svg" type="image/svg+xml">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Dashboard - Go Puppy Go</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/client-dashboard.css">
</head>
<body>
    <!-- Sidebar Toggle Button (Desktop) -->
    <button class="sidebar-toggle-btn" onclick="toggleSidebar()" title="Toggle Sidebar">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar Navigation -->
    <aside class="sidebar" id="sidebar">
        <button class="menu-toggle" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i> Menu
        </button>
        <div class="sidebar-header">
            <h2>🐾 GoPuppyGo</h2>
        </div>
        <ul class="nav-menu">
            <li><a href="browse-dogs.php"><i class="fas fa-paw"></i> Browse Dogs</a></li>
            <li><a href="my-favourites.php"><i class="fas fa-heart"></i> My Favourites</a></li>
            <li><a href="my-adoptions.php"><i class="fas fa-file-alt"></i> My Applications</a></li>
            <li><a href="my-bookings.php"><i class="fas fa-calendar-alt"></i> My Bookings</a></li>
            <li><a href="my-reviews.php"><i class="fas fa-star"></i> My Reviews</a></li>
            <li><a href="../dog-breeds.php"><i class="fas fa-search"></i> Dog Breeds</a></li>
        </ul>
    </aside>

    <!-- Main Content Area -->
    <div class="main-content">
        <!-- Header -->
        <header class="header">
            <h1 class="dashboard-title">YOUR DASHBOARD</h1>
            <br>
            <p class="welcome-message">Welcome back, <?php echo htmlspecialchars($user->getFullName()); ?>! 🐾</p>
            <a href="client-profile.php" class="header-btn">MY PROFILE</a>
            <a href="../logout.php" class="header-btn logout-btn">LOGOUT</a>
        </header>

        <!-- Dashboard Content -->
        <main class="dashboard-container">
            <!-- Statistics Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['favourites']; ?></div>
                    <div class="stat-label">Favourites</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['adoptions']; ?></div>
                    <div class="stat-label">Adoptions</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['bookings']; ?></div>
                    <div class="stat-label">Bookings</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['reviews']; ?></div>
                    <div class="stat-label">Reviews</div>
                </div>
            </div>

            <!-- Quick Actions Section -->
            <div class="dashboard-section">
                <div class="section-header">
                    <h2 class="section-title">Quick Actions</h2>
                </div>
                <div class="quick-actions">
                    <a href="browse-dogs.php" class="action-btn">
                        <i class="fas fa-search"></i>
                        <span>Browse Dogs</span>
                    </a>
                    <a href="my-favourites.php" class="action-btn">
                        <i class="fas fa-heart"></i>
                        <span>My Favourites</span>
                    </a>
                    <a href="my-adoptions.php" class="action-btn">
                        <i class="fas fa-file-alt"></i>
                        <span>My Applications</span>
                    </a>
                    <a href="my-bookings.php" class="action-btn">
                        <i class="fas fa-calendar"></i>
                        <span>My Bookings</span>
                    </a>
                </div>
            </div>

        </main>

        <!-- Footer -->
        <footer class="footer">
            © 2024 GoPuppyGo
        </footer>
    </div>

    <script src="../js/client-dashboard.js"></script>
</body>
</html>