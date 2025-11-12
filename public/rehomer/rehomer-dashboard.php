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
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #1a3a5c;
            min-height: 100vh;
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
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-info span {
            font-size: 1rem;
            color: #555;
        }

        .logout-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: background 0.3s ease;
        }

        .logout-btn:hover {
            background: #c82333;
        }

        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
        }

        .welcome-section {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
        }

        .welcome-section h2 {
            font-size: 2rem;
            color: #333;
            margin-bottom: 10px;
        }

        .welcome-section p {
            color: #666;
            font-size: 1.1rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }

        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }

        .stat-card.dogs { border-left: 4px solid #3498db; }
        .stat-card.requests { border-left: 4px solid #f39c12; }
        .stat-card.bookings { border-left: 4px solid #2ecc71; }
        .stat-card.reviews { border-left: 4px solid #e74c3c; }

        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #333;
            margin: 10px 0;
        }

        .stat-label {
            color: #666;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .section {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .section-title {
            font-size: 1.5rem;
            color: #333;
        }

        .view-all-link {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }

        .view-all-link:hover {
            text-decoration: underline;
        }

        .action-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .action-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease;
            text-decoration: none;
            color: inherit;
        }

        .action-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }

        .action-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
        }

        .action-card h3 {
            font-size: 1.2rem;
            color: #333;
            margin-bottom: 10px;
        }

        .action-card p {
            color: #666;
            font-size: 0.9rem;
        }

        .dogs-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }

        .dog-card {
            background: #f8f9fa;
            border-radius: 10px;
            overflow: hidden;
            transition: transform 0.2s;
        }

        .dog-card:hover {
            transform: translateY(-2px);
        }

        .dog-image {
            height: 180px;
            background: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
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
            top: 10px;
            right: 10px;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .dog-status.available { background: #28a745; color: white; }
        .dog-status.adopted { background: #6c757d; color: white; }

        .dog-info {
            padding: 15px;
        }

        .dog-info h4 {
            font-size: 1.1rem;
            color: #333;
            margin-bottom: 5px;
        }

        .dog-breed {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 10px;
        }

        .dog-details {
            color: #7f8c8d;
            font-size: 0.85rem;
        }

        .requests-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .request-item {
            display: flex;
            align-items: center;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
            border-left: 4px solid #f39c12;
        }

        .request-dog {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            overflow: hidden;
            margin-right: 20px;
            flex-shrink: 0;
        }

        .request-dog img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .dog-placeholder-small {
            width: 60px;
            height: 60px;
            background: #e9ecef;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: #ccc;
        }

        .request-details {
            flex: 1;
        }

        .request-details h4 {
            font-size: 1.1rem;
            color: #333;
            margin-bottom: 5px;
        }

        .request-info {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 3px;
        }

        .request-actions {
            display: flex;
            gap: 10px;
        }

        .btn-approve, .btn-reject {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            font-size: 0.85rem;
        }

        .btn-approve {
            background: #28a745;
            color: white;
        }

        .btn-approve:hover {
            background: #218838;
        }

        .btn-reject {
            background: #dc3545;
            color: white;
        }

        .btn-reject:hover {
            background: #c82333;
        }

        .bookings-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .booking-item {
            display: flex;
            align-items: center;
            padding: 20px;
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            border-radius: 12px;
            border-left: 4px solid #2196f3;
        }

        .booking-date {
            text-align: center;
            margin-right: 20px;
            flex-shrink: 0;
        }

        .booking-date .day {
            font-size: 2rem;
            font-weight: bold;
            color: #2196f3;
        }

        .booking-date .month {
            font-size: 0.8rem;
            color: #666;
            text-transform: uppercase;
        }

        .booking-details {
            flex: 1;
        }

        .booking-details h4 {
            font-size: 1.1rem;
            color: #333;
            margin-bottom: 5px;
        }

        .booking-info {
            color: #666;
            font-size: 0.9rem;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .alert-danger {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }

        @media (max-width: 768px) {
            header {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .action-cards {
                grid-template-columns: 1fr;
            }

            .dogs-grid {
                grid-template-columns: 1fr;
            }

            .request-item {
                flex-direction: column;
                text-align: center;
            }

            .request-dog {
                margin: 0 0 15px 0;
            }
        }
    </style>
</head>
<body>
    <header>
        <h1>🐾 Go Puppy Go - Rehomer Dashboard</h1>
        <div class="user-info">
            <span>Welcome, <strong><?php echo htmlspecialchars($userName); ?></strong></span>
            <form method="POST" action="../logout.php" style="display: inline;">
                <button type="submit" class="logout-btn">Logout</button>
            </form>
        </div>
    </header>

    <div class="container">
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

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card dogs">
                    <div class="stat-icon">🐶</div>
                    <div class="stat-number"><?php echo $stats['total_dogs']; ?></div>
                    <div class="stat-label">Total Dogs</div>
                    <div class="stat-details">
                        <small><?php echo $stats['available_dogs']; ?> Available • <?php echo $stats['adopted_dogs']; ?> Adopted</small>
                    </div>
                </div>

                <div class="stat-card requests">
                    <div class="stat-icon">📋</div>
                    <div class="stat-number"><?php echo $stats['pending_requests']; ?></div>
                    <div class="stat-label">Pending Requests</div>
                </div>

                <div class="stat-card bookings">
                    <div class="stat-icon">📅</div>
                    <div class="stat-number"><?php echo $stats['upcoming_bookings']; ?></div>
                    <div class="stat-label">Upcoming Bookings</div>
                </div>

                <div class="stat-card reviews">
                    <div class="stat-icon">⭐</div>
                    <div class="stat-number"><?php echo $stats['avg_rating']; ?>/5</div>
                    <div class="stat-label">Average Rating (<?php echo $stats['total_reviews']; ?> reviews)</div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="section">
                <div class="section-header">
                    <h2 class="section-title">🚀 Quick Actions</h2>
                </div>
                <div class="action-cards">
                    <a href="manage-dogs.php" class="action-card">
                        <div class="action-icon">🐶</div>
                        <h3>Manage Dogs</h3>
                        <p>Add, edit, or remove dogs from your listing</p>
                    </a>

                    <a href="adoption-requests.php" class="action-card">
                        <div class="action-icon">📋</div>
                        <h3>Adoption Requests</h3>
                        <p>Review and respond to adoption applications</p>
                    </a>

                    <a href="manage-bookings.php" class="action-card">
                        <div class="action-icon">📅</div>
                        <h3>Manage Bookings</h3>
                        <p>View and manage scheduled visits</p>
                    </a>

                    <a href="profile-settings.php" class="action-card">
                        <div class="action-icon">⚙️</div>
                        <h3>Profile Settings</h3>
                        <p>Update your rehoming center information</p>
                    </a>

                    <a href="reviews.php" class="action-card">
                        <div class="action-icon">⭐</div>
                        <h3>Reviews</h3>
                        <p>View feedback from clients</p>
                    </a>

                    <a href="../support.php" class="action-card">
                        <div class="action-icon">📞</div>
                        <h3>Support</h3>
                        <p>Contact admin or view FAQs</p>
                    </a>
                </div>
            </div>

            <!-- Recent Dogs -->
            <?php if (!empty($recentDogs)): ?>
            <div class="section">
                <div class="section-header">
                    <h2 class="section-title">🐕 Recent Dogs</h2>
                    <a href="manage-dogs.php" class="view-all-link">View All</a>
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
                            <div class="dog-status <?php echo strtolower($dog['status']); ?>">
                                <?php echo $dog['status']; ?>
                            </div>
                        </div>
                        <div class="dog-info">
                            <h4><?php echo htmlspecialchars($dog['name']); ?></h4>
                            <p class="dog-breed"><?php echo htmlspecialchars($dog['breed_name']); ?></p>
                            <p class="dog-details">
                                <?php echo $dog['age'] ? $dog['age'] . ' years old' : 'Age unknown'; ?> •
                                <?php echo $dog['gender_name'] ?? 'Unknown gender'; ?> •
                                $<?php echo number_format($dog['adoption_fee'], 2); ?>
                            </p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Pending Requests -->
            <?php if (!empty($pendingRequests)): ?>
            <div class="section">
                <div class="section-header">
                    <h2 class="section-title">📋 Pending Adoption Requests</h2>
                    <a href="adoption-requests.php" class="view-all-link">View All</a>
                </div>
                <div class="requests-list">
                    <?php foreach ($pendingRequests as $request): ?>
                    <div class="request-item">
                        <div class="request-dog">
                            <?php if ($request['image_url']): ?>
                                <img src="<?php echo htmlspecialchars($request['image_url']); ?>" alt="<?php echo htmlspecialchars($request['dog_name']); ?>">
                            <?php else: ?>
                                <div class="dog-placeholder-small">🐕</div>
                            <?php endif; ?>
                        </div>
                        <div class="request-details">
                            <h4><?php echo htmlspecialchars($request['dog_name']); ?></h4>
                            <p class="request-info"><strong>Client:</strong> <?php echo htmlspecialchars($request['client_name']); ?></p>
                            <p class="request-info"><strong>Email:</strong> <?php echo htmlspecialchars($request['client_email']); ?></p>
                            <p class="request-info"><strong>Applied:</strong> <?php echo date('M j, Y g:i A', strtotime($request['applied_at'])); ?></p>
                            <?php if ($request['message']): ?>
                                <p class="request-info"><strong>Message:</strong> <?php echo htmlspecialchars($request['message']); ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="request-actions">
                            <button class="btn-approve" onclick="handleRequest(<?php echo $request['adoption_id']; ?>, 'approve')">Approve</button>
                            <button class="btn-reject" onclick="handleRequest(<?php echo $request['adoption_id']; ?>, 'reject')">Reject</button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Upcoming Bookings -->
            <?php if (!empty($upcomingBookings)): ?>
            <div class="section">
                <div class="section-header">
                    <h2 class="section-title">📅 Upcoming Bookings</h2>
                    <a href="manage-bookings.php" class="view-all-link">View All</a>
                </div>
                <div class="bookings-list">
                    <?php foreach ($upcomingBookings as $booking): ?>
                    <div class="booking-item">
                        <div class="booking-date">
                            <div class="day"><?php echo date('j', strtotime($booking['booking_date'])); ?></div>
                            <div class="month"><?php echo date('M', strtotime($booking['booking_date'])); ?></div>
                        </div>
                        <div class="booking-details">
                            <h4>Visit with <?php echo htmlspecialchars($booking['dog_name']); ?></h4>
                            <p class="booking-info"><strong>Client:</strong> <?php echo htmlspecialchars($booking['client_name']); ?></p>
                            <p class="booking-info"><strong>Email:</strong> <?php echo htmlspecialchars($booking['client_email']); ?></p>
                            <p class="booking-info"><strong>Time:</strong> <?php echo date('g:i A', strtotime($booking['booking_date'])); ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

        <?php endif; ?>
    </div>

    <script>
        function handleRequest(adoptionId, action) {
            if (confirm(`Are you sure you want to ${action} this adoption request?`)) {
                // You can implement AJAX call here
                window.location.href = `handle-adoption.php?id=${adoptionId}&action=${action}`;
            }
        }

        // Auto-refresh every 5 minutes
        setTimeout(function() {
            location.reload();
        }, 300000);
    </script>
</body>
</html>