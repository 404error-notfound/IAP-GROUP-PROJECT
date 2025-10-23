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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Dashboard - Go Puppy Go</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        /* Dashboard-specific styles extending the global layout */
        
        .dashboard-content {
            padding: 1rem;
        }

        .welcome-card {
            margin-bottom: 2rem;
        }

        .welcome-title {
            font-size: 2em;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .welcome-subtitle {
            color: #666;
            margin-bottom: 1rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin: 1rem 0;
        }

        .stat-card {
            text-align: center;
            transition: transform 0.2s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
        }

        .stat-card.favorites { border-left: 4px solid #e74c3c; }
        .stat-card.adoptions { border-left: 4px solid #3498db; }
        .stat-card.bookings { border-left: 4px solid #f39c12; }
        .stat-card.reviews { border-left: 4px solid #2ecc71; }

        .stat-icon {
            font-size: 2em;
            margin-bottom: 0.5rem;
            display: block;
        }

        .stat-number {
            font-size: 2em;
            font-weight: bold;
            color: #333;
            margin: 0.5rem 0;
        }

        .stat-label {
            color: #666;
            font-weight: 500;
            font-size: 0.9em;
        }

        .preferences-card {
            background: #f8f9fa;
            border-left: 3px solid #6c757d;
            margin-top: 1rem;
        }

        .preferences-content {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }

        .pref-tag {
            background: #e9ecef;
            padding: 0.25rem 0.75rem;
            border-radius: 0.75rem;
            font-size: 0.85rem;
            color: #495057;
        }

        .dashboard-sections {
            display: grid;
            gap: 2rem;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .section-title {
            font-size: 1.25rem;
            color: #333;
            margin: 0;
        }

        .view-all-link {
            color: #007bff;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .view-all-link:hover {
            text-decoration: underline;
        }

        .action-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
        }

        .action-card {
            text-decoration: none;
            transition: transform 0.2s;
            text-align: center;
            color: inherit;
        }

        .action-card:hover {
            transform: translateY(-2px);
            text-decoration: none;
        }

        .action-icon {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            display: block;
        }

        .action-card h3 {
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
            color: #333;
        }

        .action-card p {
            color: #666;
            font-size: 0.85rem;
            line-height: 1.4;
            margin: 0;
        }

        /* Dogs Grid */
        .dogs-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1rem;
        }

        .dog-card {
            overflow: hidden;
            transition: transform 0.2s;
        }

        .dog-card:hover {
            transform: translateY(-2px);
        }

        .dog-image {
            position: relative;
            height: 180px;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .dog-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .dog-placeholder {
            font-size: 3rem;
            color: #ccc;
        }

        .dog-status {
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            padding: 0.25rem 0.5rem;
            border-radius: 0.75rem;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .dog-status.available { background: #28a745; color: white; }
        .dog-status.adopted { background: #6c757d; color: white; }

        .dog-info {
            padding: 1rem;
        }

        .dog-info h3 {
            font-size: 1.1rem;
            color: #333;
            margin-bottom: 0.25rem;
        }

        .dog-breed {
            color: #666;
            font-weight: 500;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        .dog-details, .dog-location {
            font-size: 14px;
            color: #7f8c8d;
            margin-bottom: 5px;
        }

        /* Requests List */
        .requests-list {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .request-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 0.5rem;
            border-left: 3px solid #007bff;
        }

        .request-dog {
            width: 3rem;
            height: 3rem;
            border-radius: 50%;
            overflow: hidden;
            margin-right: 1rem;
            flex-shrink: 0;
        }

        .request-dog img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .dog-placeholder-small {
            width: 3rem;
            height: 3rem;
            background: #e9ecef;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: #ccc;
        }

        .request-details {
            flex: 1;
        }

        .request-details h4 {
            font-size: 1rem;
            color: #333;
            margin-bottom: 0.25rem;
        }

        .request-breed, .request-location, .request-date {
            font-size: 0.85rem;
            color: #666;
            margin-bottom: 0.125rem;
        }

        .request-status {
            margin-left: 1rem;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-badge.pending { background: #f39c12; color: white; }
        .status-badge.approved { background: #2ecc71; color: white; }
        .status-badge.rejected { background: #e74c3c; color: white; }
        .status-badge.confirmed { background: #2ecc71; color: white; }
        .status-badge.cancelled { background: #95a5a6; color: white; }

        /* Bookings List */
        .bookings-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .booking-item {
            display: flex;
            align-items: center;
            padding: 20px;
            background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%);
            border-radius: 12px;
            border-left: 4px solid #f39c12;
        }

        .booking-date {
            text-align: center;
            margin-right: 20px;
            flex-shrink: 0;
        }

        .booking-date .day {
            font-size: 2em;
            font-weight: bold;
            color: #f39c12;
            line-height: 1;
        }

        .booking-date .month {
            font-size: 12px;
            color: #7f8c8d;
            text-transform: uppercase;
            font-weight: 600;
        }

        .booking-details {
            flex: 1;
        }

        .booking-details h4 {
            font-size: 1.2em;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .booking-info, .booking-time, .booking-rehomer {
            font-size: 14px;
            color: #7f8c8d;
            margin-bottom: 3px;
        }

        .upcoming-section {
            border: 2px solid #f39c12;
            background: linear-gradient(135deg, #fff9e6 0%, #fff3d4 100%);
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
            .user-stats {
                grid-template-columns: repeat(2, 1fr);
            }

            .action-cards {
                grid-template-columns: 1fr;
            }

            .dogs-grid {
                grid-template-columns: 1fr;
            }

            .section-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.75rem;
            }

            .request-item {
                flex-direction: column;
                text-align: center;
                gap: 0.75rem;
            }

            .request-dog {
                margin: 0;
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
                    <li><a href="client-dashboard.php">Dashboard</a></li>
                    <li><a href="../puppy/BrowsePuppy.php">Browse Dogs</a></li>
                    <li><a href="my-favourites.php">My Favourites</a></li>
                    <li><a href="my-adoptions.php">Applications</a></li>
                    <li><a href="my-bookings.php">Bookings</a></li>
                </ul>
            </nav>
            <div class="user-menu">
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($user->getFullName(), 0, 1)); ?>
                    </div>
                    <span><?php echo htmlspecialchars($user->getFullName()); ?></span>
                </div>
                <form method="POST" action="../logout.php" style="display: inline;">
                    <button type="submit" class="logout-btn">Logout</button>
                </form>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="welcome-section">
            <h1 class="welcome-title">Welcome back, <?php echo htmlspecialchars($user->getFullName()); ?>! �</h1>
            <p class="welcome-subtitle">Find your perfect furry companion and track your adoption journey</p>
            
            <div class="user-stats">
                <div class="stat-card favorites">
                    <div class="stat-icon">❤️</div>
                    <div class="stat-number"><?php echo $stats['favourites']; ?></div>
                    <div class="stat-label">Favourites</div>
                </div>
                <div class="stat-card adoptions">
                    <div class="stat-icon">📋</div>
                    <div class="stat-number"><?php echo $stats['adoptions']; ?></div>
                    <div class="stat-label">Adoption Requests</div>
                </div>
                <div class="stat-card bookings">
                    <div class="stat-icon">📅</div>
                    <div class="stat-number"><?php echo $stats['bookings']; ?></div>
                    <div class="stat-label">Bookings Made</div>
                </div>
                <div class="stat-card reviews">
                    <div class="stat-icon">⭐</div>
                    <div class="stat-number"><?php echo $stats['reviews']; ?></div>
                    <div class="stat-label">Reviews Made</div>
                </div>
            </div>
            
            <?php if ($preferences): ?>
            <div class="preferences-banner">
                <h3>🎯 Your Preferences</h3>
                <div class="preferences-content">
                    <?php if (isset($preferences['breeds']) && !empty($preferences['breeds'])): ?>
                        <span class="pref-tag">🐕 <?php echo implode(', ', $preferences['breeds']); ?></span>
                    <?php endif; ?>
                    <?php if (isset($preferences['age']) && $preferences['age']): ?>
                        <span class="pref-tag">📅 <?php echo $preferences['age']; ?> years old</span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="dashboard-sections">
            <!-- Quick Actions Section -->
            <div class="card">
                <div class="section-header">
                    <h2 class="section-title">🚀 Quick Actions</h2>
                </div>
                <div class="action-cards">
                    <a href="../puppy/BrowsePuppy.php" class="action-card card">
                        <div class="action-icon">🔍</div>
                        <h3>Browse Dogs</h3>
                        <p>Discover amazing dogs looking for their forever homes</p>
                    </a>

                    <a href="my-favourites.php" class="action-card card">
                        <div class="action-icon">❤️</div>
                        <h3>My Favourites</h3>
                        <p>View all the dogs you've marked as favorites</p>
                    </a>

                    <a href="my-adoptions.php" class="action-card card">
                        <div class="action-icon">📋</div>
                        <h3>My Applications</h3>
                        <p>Track your adoption requests and their status</p>
                    </a>

                    <a href="my-bookings.php" class="action-card card">
                        <div class="action-icon">📅</div>
                        <h3>My Bookings</h3>
                        <p>Manage your scheduled visits with dogs</p>
                    </a>

                    <a href="profile-settings.php" class="action-card card">
                        <div class="action-icon">👤</div>
                        <h3>Profile Settings</h3>
                        <p>Update your preferences and account information</p>
                    </a>

                    <a href="my-reviews.php" class="action-card card">
                        <div class="action-icon">⭐</div>
                        <h3>My Reviews</h3>
                        <p>View and manage your reviews of rehomers</p>
                    </a>
                </div>
            </div>

            <!-- Favourites Section -->
            <?php if (!empty($recentFavourites)): ?>
            <div class="card">
                <div class="section-header">
                    <h2 class="section-title">❤️ Recent Favourites</h2>
                    <a href="my-favourites.php" class="view-all-link">View All</a>
                </div>
                <div class="dogs-grid">
                    <?php foreach ($recentFavourites as $fav): ?>
                    <div class="dog-card card">
                        <div class="dog-image">
                            <?php if ($fav['image_url']): ?>
                                <img src="<?php echo htmlspecialchars($fav['image_url']); ?>" alt="<?php echo htmlspecialchars($fav['name']); ?>">
                            <?php else: ?>
                                <div class="dog-placeholder">🐕</div>
                            <?php endif; ?>
                            <div class="dog-status <?php echo strtolower($fav['status']); ?>"><?php echo $fav['status']; ?></div>
                        </div>
                        <div class="dog-info">
                            <h3><?php echo htmlspecialchars($fav['name']); ?></h3>
                            <p class="dog-breed"><?php echo htmlspecialchars($fav['breed_name']); ?></p>
                            <p class="dog-details">
                                <?php echo $fav['age'] ? $fav['age'] . ' years old' : 'Age unknown'; ?> • 
                                $<?php echo number_format($fav['adoption_fee'], 2); ?>
                            </p>
                            <p class="dog-location">📍 <?php echo htmlspecialchars($fav['location']); ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Adoption Requests Section -->
            <?php if (!empty($adoptionRequests)): ?>
            <div class="card">
                <div class="section-header">
                    <h2 class="section-title">📋 Recent Adoption Requests</h2>
                    <a href="my-adoptions.php" class="view-all-link">View All</a>
                </div>
                <div class="requests-list">
                    <?php foreach ($adoptionRequests as $request): ?>
                    <div class="request-item">
                        <div class="request-dog">
                            <?php if ($request['image_url']): ?>
                                <img src="<?php echo htmlspecialchars($request['image_url']); ?>" alt="<?php echo htmlspecialchars($request['name']); ?>">
                            <?php else: ?>
                                <div class="dog-placeholder-small">�</div>
                            <?php endif; ?>
                        </div>
                        <div class="request-details">
                            <h4><?php echo htmlspecialchars($request['name']); ?></h4>
                            <p class="request-breed"><?php echo htmlspecialchars($request['breed_name']); ?> • <?php echo $request['age'] ? $request['age'] . ' years' : 'Age unknown'; ?></p>
                            <p class="request-location">📍 <?php echo htmlspecialchars($request['rehomer_location']); ?></p>
                            <p class="request-date">Applied: <?php echo date('M j, Y', strtotime($request['applied_at'])); ?></p>
                        </div>
                        <div class="request-status">
                            <span class="status-badge <?php echo strtolower($request['status']); ?>">
                                <?php echo $request['status']; ?>
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Upcoming Bookings Section -->
            <?php if (!empty($upcomingBookings)): ?>
            <div class="card">
                <div class="section-header">
                    <h2 class="section-title">📅 Upcoming Bookings (Next 7 Days)</h2>
                    <a href="my-bookings.php" class="view-all-link">View All</a>
                </div>
                <div class="bookings-list">
                    <?php foreach ($upcomingBookings as $booking): ?>
                    <div class="booking-item">
                        <div class="booking-date">
                            <div class="day"><?php echo date('j', strtotime($booking['booking_date'])); ?></div>
                            <div class="month"><?php echo date('M', strtotime($booking['booking_date'])); ?></div>
                        </div>
                        <div class="booking-details">
                            <h4>Visit with <?php echo htmlspecialchars($booking['name']); ?></h4>
                            <p class="booking-info"><?php echo htmlspecialchars($booking['breed_name']); ?> • <?php echo $booking['age'] ? $booking['age'] . ' years' : 'Age unknown'; ?></p>
                            <p class="booking-time">⏰ <?php echo date('g:i A', strtotime($booking['booking_date'])); ?></p>
                            <p class="booking-rehomer">👤 <?php echo htmlspecialchars($booking['rehomer_name']); ?></p>
                        </div>
                        <div class="booking-status">
                            <span class="status-badge <?php echo strtolower($booking['status']); ?>">
                                <?php echo $booking['status']; ?>
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
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