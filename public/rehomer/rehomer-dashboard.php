<?php
// rehomer-dashboard.php
// Path: public/rehomer/rehomer-dashboard.php
require_once __DIR__ . '/../../bootstrap.php';

use Angel\IapGroupProject\Database;

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    $_SESSION['redirect_after_login'] = 'rehomer/rehomer-dashboard.php';
    header("Location: ../login.php");
    exit;
}

// Check if user is a rehomer
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'rehomer') {
    header("Location: ../login.php");
    exit;
}

// Get user information from session
$userId = $_SESSION['user_id'];
$userName = $_SESSION['user_name'];
$userEmail = $_SESSION['user_email'];

// Get database connection
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

    // Get rehomer ID and details
    $stmt = $pdo->prepare("
        SELECT rehomer_id, license_number, location, contact_email 
        FROM rehomers 
        WHERE user_id = ?
    ");
    $stmt->execute([$userId]);
    $rehomerData = $stmt->fetch();

    if (!$rehomerData) {
        throw new Exception("Rehomer profile not found.");
    }

    $rehomerId = $rehomerData['rehomer_id'];

    // Get dashboard statistics
    $stats = [];

    // Total Dogs Listed
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM dogs WHERE rehomer_id = ?");
    $stmt->execute([$rehomerId]);
    $stats['total_dogs'] = $stmt->fetchColumn();

    // Available Dogs
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM dogs WHERE rehomer_id = ? AND status = 'Available'");
    $stmt->execute([$rehomerId]);
    $stats['available_dogs'] = $stmt->fetchColumn();

    // Adopted Dogs
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM dogs WHERE rehomer_id = ? AND status = 'Adopted'");
    $stmt->execute([$rehomerId]);
    $stats['adopted_dogs'] = $stmt->fetchColumn();

    // Pending Adoption Requests
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count 
        FROM adoptions a
        JOIN dogs d ON a.dog_id = d.dog_id
        WHERE d.rehomer_id = ? AND a.status = 'Pending'
    ");
    $stmt->execute([$rehomerId]);
    $stats['pending_requests'] = $stmt->fetchColumn();

    // Upcoming Bookings
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count 
        FROM bookings 
        WHERE rehomer_id = ? AND booking_date >= NOW() AND status = 'Confirmed'
    ");
    $stmt->execute([$rehomerId]);
    $stats['upcoming_bookings'] = $stmt->fetchColumn();

    // Total Reviews Received
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM reviews WHERE rehomer_id = ?");
    $stmt->execute([$rehomerId]);
    $stats['total_reviews'] = $stmt->fetchColumn();

    // Average Rating
    $stmt = $pdo->prepare("SELECT AVG(rating) as avg_rating FROM reviews WHERE rehomer_id = ?");
    $stmt->execute([$rehomerId]);
    $avgRating = $stmt->fetchColumn();
    $stats['avg_rating'] = $avgRating ? round($avgRating, 1) : 0;

    // Get recent dogs
    $stmt = $pdo->prepare("
        SELECT d.dog_id, d.name, d.age, d.status, d.image_url, d.adoption_fee, d.created_at,
               b.breed_name, dg.gender_name
        FROM dogs d
        JOIN breeds b ON d.breed_id = b.breed_id
        LEFT JOIN dog_gender dg ON d.dog_gender_id = dg.dog_gender_id
        WHERE d.rehomer_id = ?
        ORDER BY d.created_at DESC
        LIMIT 5
    ");
    $stmt->execute([$rehomerId]);
    $recentDogs = $stmt->fetchAll();

    // Get pending adoption requests
    $stmt = $pdo->prepare("
        SELECT a.adoption_id, a.status, a.message, a.applied_at,
               d.dog_id, d.name as dog_name, d.image_url,
               b.breed_name,
               u.full_name as client_name, u.email as client_email
        FROM adoptions a
        JOIN dogs d ON a.dog_id = d.dog_id
        JOIN breeds b ON d.breed_id = b.breed_id
        JOIN clients c ON a.client_id = c.client_id
        JOIN users u ON c.user_id = u.user_id
        WHERE d.rehomer_id = ? AND a.status = 'Pending'
        ORDER BY a.applied_at DESC
        LIMIT 5
    ");
    $stmt->execute([$rehomerId]);
    $pendingRequests = $stmt->fetchAll();

    // Get upcoming bookings
    $stmt = $pdo->prepare("
        SELECT bo.booking_id, bo.booking_date, bo.status,
               d.name as dog_name, d.image_url,
               b.breed_name,
               u.full_name as client_name, u.email as client_email
        FROM bookings bo
        JOIN dogs d ON bo.dog_id = d.dog_id
        JOIN breeds b ON d.breed_id = b.breed_id
        JOIN clients c ON bo.client_id = c.client_id
        JOIN users u ON c.user_id = u.user_id
        WHERE bo.rehomer_id = ? AND bo.booking_date >= NOW()
        ORDER BY bo.booking_date ASC
        LIMIT 5
    ");
    $stmt->execute([$rehomerId]);
    $upcomingBookings = $stmt->fetchAll();

} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
    error_log("Rehomer Dashboard Error: " . $e->getMessage());
} catch (Exception $e) {
    $error = $e->getMessage();
    error_log("Rehomer Dashboard Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" href="/IAP-GROUP-PROJECT/public/images/gopuppygo-logo.svg" type="image/svg+xml">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rehomer Dashboard - Go Puppy Go</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/rehomer-dashboard.css">
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
            <li><a href="manage-dogs.php"><i class="fas fa-dog"></i> Manage Dogs</a></li>
            <li><a href="adoption-requests.php"><i class="fas fa-clipboard-list"></i> Adoption Requests</a></li>
            <li><a href="manage-bookings.php"><i class="fas fa-calendar-check"></i> Manage Bookings</a></li>
            <li><a href="reviews.php"><i class="fas fa-star"></i> Reviews</a></li>
            <li><a href="profile-settings.php"><i class="fas fa-cog"></i> Profile Settings</a></li>
            <li><a href="../puppy/AddPuppy.php"><i class="fas fa-plus-circle"></i> Add New Dog</a></li>
        </ul>
    </aside>

    <!-- Main Content Area -->
    <div class="main-content">
        <!-- Header -->
        <header class="header">
            <div class="header-left">
                <h1 class="dashboard-title">REHOMER DASHBOARD</h1>
                <p class="welcome-message">Welcome back, <?php echo htmlspecialchars($userName); ?>! 🐾</p>
            </div>
            <div class="header-right">
                <a href="profile-settings.php" class="header-btn">MY PROFILE</a>
                <a href="../logout.php" class="header-btn logout-btn">LOGOUT</a>
            </div>
        </header>

        <!-- Dashboard Content -->
        <main class="dashboard-container">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php else: ?>

            <div class="welcome-section">
                <h2>Hello <?php echo htmlspecialchars($userName); ?>! 👋</h2>
                <p>Here's what's happening in your rehoming center today.</p>
                <p><strong>License:</strong> <?php echo htmlspecialchars($rehomerData['license_number']); ?> | 
                   <strong>Location:</strong> <?php echo htmlspecialchars($rehomerData['location']); ?></p>
            </div>

            <!-- Statistics Grid -->
            <div class="stats-grid">
                <div class="stat-card dogs">
                    <div class="stat-number"><?php echo $stats['total_dogs']; ?></div>
                    <div class="stat-label">Total Dogs</div>
                    <div class="stat-details">
                        <small><?php echo $stats['available_dogs']; ?> Available • <?php echo $stats['adopted_dogs']; ?> Adopted</small>
                    </div>
                </div>
                <div class="stat-card requests">
                    <div class="stat-number"><?php echo $stats['pending_requests']; ?></div>
                    <div class="stat-label">Pending Requests</div>
                </div>
                <div class="stat-card bookings">
                    <div class="stat-number"><?php echo $stats['upcoming_bookings']; ?></div>
                    <div class="stat-label">Upcoming Bookings</div>
                </div>
                <div class="stat-card reviews">
                    <div class="stat-number"><?php echo $stats['avg_rating']; ?>/5</div>
                    <div class="stat-label">Avg Rating (<?php echo $stats['total_reviews']; ?> reviews)</div>
                </div>
            </div>

            <!-- Quick Actions Section -->
            <div class="dashboard-section">
                <div class="section-header">
                    <h2 class="section-title">Quick Actions</h2>
                </div>
                <div class="quick-actions">
                    <a href="manage-dogs.php" class="action-btn">
                        <i class="fas fa-dog"></i>
                        <span>Manage Dogs</span>
                    </a>
                    <a href="adoption-requests.php" class="action-btn">
                        <i class="fas fa-clipboard-list"></i>
                        <span>Adoption Requests</span>
                    </a>
                    <a href="manage-bookings.php" class="action-btn">
                        <i class="fas fa-calendar-check"></i>
                        <span>Manage Bookings</span>
                    </a>
                    <a href="reviews.php" class="action-btn">
                        <i class="fas fa-star"></i>
                        <span>Reviews</span>
                    </a>
                    <a href="profile-settings.php" class="action-btn">
                        <i class="fas fa-cog"></i>
                        <span>Profile Settings</span>
                    </a>
                    <a href="../puppy/AddPuppy.php" class="action-btn">
                        <i class="fas fa-plus-circle"></i>
                        <span>Add New Dog</span>
                    </a>
                </div>
            </div>

            <!-- Recent Dogs -->
            <?php if (!empty($recentDogs)): ?>
            <div class="dashboard-section">
                <div class="section-header">
                    <h2 class="section-title">Recent Dogs</h2>
                    <a href="manage-dogs.php" class="section-link">View All →</a>
                </div>
                <div class="dogs-grid">
                    <?php foreach ($recentDogs as $dog): ?>
                    <div class="dog-card">
                        <div class="dog-image">
                            <?php if ($dog['image_url']): ?>
                                <img src="<?php echo htmlspecialchars($dog['image_url']); ?>" alt="<?php echo htmlspecialchars($dog['name']); ?>">
                            <?php else: ?>
                                <div class="dog-placeholder">🐕</div>
                            <?php endif; ?>
                            <span class="dog-status <?php echo strtolower($dog['status']); ?>">
                                <?php echo htmlspecialchars($dog['status']); ?>
                            </span>
                        </div>
                        <div class="dog-info">
                            <h3 class="dog-name"><?php echo htmlspecialchars($dog['name']); ?></h3>
                            <p class="dog-breed"><?php echo htmlspecialchars($dog['breed']); ?></p>
                            <div class="dog-details">
                                <span class="dog-tag"><?php echo htmlspecialchars($dog['age']); ?></span>
                                <span class="dog-tag"><?php echo htmlspecialchars($dog['gender']); ?></span>
                                <span class="dog-tag"><?php echo htmlspecialchars($dog['size']); ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Pending Adoption Requests -->
            <?php if (!empty($pendingRequests)): ?>
            <div class="dashboard-section">
                <div class="section-header">
                    <h2 class="section-title">Pending Adoption Requests</h2>
                    <a href="adoption-requests.php" class="section-link">View All →</a>
                </div>
                <div class="requests-list">
                    <?php foreach ($pendingRequests as $request): ?>
                    <div class="request-card">
                        <div class="request-info">
                            <h4><?php echo htmlspecialchars($request['dog_name']); ?></h4>
                            <p><strong>Applicant:</strong> <?php echo htmlspecialchars($request['client_name']); ?></p>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($request['client_email']); ?></p>
                            <p><strong>Applied:</strong> <?php echo date('M d, Y', strtotime($request['request_date'])); ?></p>
                        </div>
                        <div class="request-actions">
                            <button class="action-btn success" onclick="handleRequest(<?php echo $request['id']; ?>, 'approve')">
                                <i class="fas fa-check"></i>
                                <span>Approve</span>
                            </button>
                            <button class="action-btn danger" onclick="handleRequest(<?php echo $request['id']; ?>, 'decline')">
                                <i class="fas fa-times"></i>
                                <span>Decline</span>
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Upcoming Bookings -->
            <?php if (!empty($upcomingBookings)): ?>
            <div class="dashboard-section">
                <div class="section-header">
                    <h2 class="section-title">Upcoming Bookings</h2>
                    <a href="manage-bookings.php" class="section-link">View All →</a>
                </div>
                <div class="bookings-list">
                    <?php foreach ($upcomingBookings as $booking): ?>
                    <div class="booking-card">
                        <div class="booking-date">
                            <div class="date-day"><?php echo date('d', strtotime($booking['booking_date'])); ?></div>
                            <div class="date-month"><?php echo date('M', strtotime($booking['booking_date'])); ?></div>
                        </div>
                        <div class="booking-info">
                            <h4>Visit with <?php echo htmlspecialchars($booking['dog_name']); ?></h4>
                            <p><strong>Client:</strong> <?php echo htmlspecialchars($booking['client_name']); ?></p>
                            <p><strong>Time:</strong> <?php echo date('g:i A', strtotime($booking['booking_date'])); ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php endif; ?>

        </main>

        <!-- Footer -->
        <footer class="footer">
            © 2024 GoPuppyGo
        </footer>
    </div>

    <script src="../js/rehomer-dashboard.js"></script>
</body>
</html>