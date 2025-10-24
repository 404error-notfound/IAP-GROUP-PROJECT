<?php
require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/../../src/Layouts/PuppyLayout.php';
require_once __DIR__ . '/../../src/Database.php';
require_once __DIR__. '/../../vendor/autoload.php';

use Angel\IapGroupProject\Database;

// Simple authentication check (in production, you'd use proper admin auth)
$isAdmin = true; // For demo purposes

if (!$isAdmin) {
    header('Location: login.php');
    exit;
}

try {
    $db = Database::getInstance()->getConnection();
    
    // Get all users with their roles
    $stmt = $db->query("
        SELECT u.user_id, u.full_name, u.email, ur.role_name, ug.gender_name, u.verified, u.created_at 
        FROM users u 
        LEFT JOIN user_roles ur ON u.role_id = ur.role_id 
        LEFT JOIN user_gender ug ON u.gender_id = ug.gender_id 
        ORDER BY u.created_at DESC
    ");
    $users = $stmt->fetchAll();
    
    // Get clients with preferences
    $stmt = $db->query("
        SELECT c.client_id, c.user_id, u.full_name, u.email, c.dog_preferences 
        FROM clients c 
        JOIN users u ON c.user_id = u.user_id
        ORDER BY c.client_id DESC
    ");
    $clients = $stmt->fetchAll();
    
    // Get rehomers with license info
    $stmt = $db->query("
        SELECT r.rehomer_id, r.user_id, u.full_name, u.email, r.license_number, r.location, r.contact_email 
        FROM rehomers r 
        JOIN users u ON r.user_id = u.user_id
        ORDER BY r.rehomer_id DESC
    ");
    $rehomers = $stmt->fetchAll();
    
    // Get admins with access codes
    $stmt = $db->query("
        SELECT a.admin_id, a.user_id, u.full_name, u.email, a.access_code 
        FROM admin a 
        JOIN users u ON a.user_id = u.user_id
        ORDER BY a.admin_id DESC
    ");
    $admins = $stmt->fetchAll();
    
} catch (Exception $e) {
    $error = "Database error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Go Puppy Go</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .dashboard-header {
            background: linear-gradient(135deg, #4472c4 0%, #365a96 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        .stats-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 1.5rem;
            margin-bottom: 1rem;
        }
        .stats-number {
            font-size: 2rem;
            font-weight: bold;
            color: #4472c4;
        }
        .table-section {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .section-title {
            color: #4472c4;
            border-bottom: 2px solid #4472c4;
            padding-bottom: 0.5rem;
            margin-bottom: 1rem;
        }
        .badge-verified {
            background: #28a745;
        }
        .badge-unverified {
            background: #dc3545;
        }
        .access-code {
            font-family: 'Courier New', monospace;
            background: #f8f9fa;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            border: 1px solid #dee2e6;
        }
    </style>
</head>
<body style="background-color: #f8f9fa;">

<div class="dashboard-header">
    <div class="container">
        <h1><i class="bi bi-shield-check"></i> Admin Dashboard</h1>
        <p class="mb-0">Go Puppy Go - User Management System</p>
    </div>
</div>

<div class="container">
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php else: ?>
        
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stats-card text-center">
                    <div class="stats-number"><?php echo count($users); ?></div>
                    <div>Total Users</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card text-center">
                    <div class="stats-number"><?php echo count($clients); ?></div>
                    <div>Clients</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card text-center">
                    <div class="stats-number"><?php echo count($rehomers); ?></div>
                    <div>Rehomers</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card text-center">
                    <div class="stats-number"><?php echo count($admins); ?></div>
                    <div>Admins</div>
                </div>
            </div>
        </div>

        <!-- All Users Table -->
        <div class="table-section">
            <h3 class="section-title">👥 All Users</h3>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Gender</th>
                            <th>Status</th>
                            <th>Registration Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                            <tr><td colspan="7" class="text-center text-muted">No users found</td></tr>
                        <?php else: ?>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo $user['user_id']; ?></td>
                                    <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $user['role_name'] === 'admin' ? 'danger' : ($user['role_name'] === 'rehomer' ? 'warning' : 'primary'); ?>">
                                            <?php echo ucfirst($user['role_name']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $user['gender_name'] ?: 'N/A'; ?></td>
                                    <td>
                                        <span class="badge <?php echo $user['verified'] ? 'badge-verified' : 'badge-unverified'; ?>">
                                            <?php echo $user['verified'] ? 'Verified' : 'Unverified'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M j, Y g:i A', strtotime($user['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Clients Table -->
        <div class="table-section">
            <h3 class="section-title">👤 Clients</h3>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-primary">
                        <tr>
                            <th>Client ID</th>
                            <th>User ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Dog Preferences</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($clients)): ?>
                            <tr><td colspan="5" class="text-center text-muted">No clients found</td></tr>
                        <?php else: ?>
                            <?php foreach ($clients as $client): ?>
                                <tr>
                                    <td><?php echo $client['client_id']; ?></td>
                                    <td><?php echo $client['user_id']; ?></td>
                                    <td><?php echo htmlspecialchars($client['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($client['email']); ?></td>
                                    <td>
                                        <?php 
                                        if ($client['dog_preferences']) {
                                            $prefs = json_decode($client['dog_preferences'], true);
                                            if ($prefs) {
                                                echo '<small>';
                                                if (isset($prefs['breeds'])) {
                                                    echo '<strong>Breeds:</strong> ' . implode(', ', $prefs['breeds']) . '<br>';
                                                }
                                                if (isset($prefs['age'])) {
                                                    echo '<strong>Age:</strong> ' . $prefs['age'] . ' years';
                                                }
                                                echo '</small>';
                                            } else {
                                                echo '<em>Invalid JSON</em>';
                                            }
                                        } else {
                                            echo '<em>None specified</em>';
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Rehomers Table -->
        <div class="table-section">
            <h3 class="section-title">🏠 Rehomers</h3>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-warning">
                        <tr>
                            <th>Rehomer ID</th>
                            <th>User ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>License Number</th>
                            <th>Location</th>
                            <th>Contact Email</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($rehomers)): ?>
                            <tr><td colspan="7" class="text-center text-muted">No rehomers found</td></tr>
                        <?php else: ?>
                            <?php foreach ($rehomers as $rehomer): ?>
                                <tr>
                                    <td><?php echo $rehomer['rehomer_id']; ?></td>
                                    <td><?php echo $rehomer['user_id']; ?></td>
                                    <td><?php echo htmlspecialchars($rehomer['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($rehomer['email']); ?></td>
                                    <td><code><?php echo htmlspecialchars($rehomer['license_number']); ?></code></td>
                                    <td><?php echo htmlspecialchars($rehomer['location']); ?></td>
                                    <td><?php echo htmlspecialchars($rehomer['contact_email']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Admins Table -->
        <div class="table-section">
            <h3 class="section-title">👑 Administrators</h3>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-danger">
                        <tr>
                            <th>Admin ID</th>
                            <th>User ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Access Code</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($admins)): ?>
                            <tr><td colspan="5" class="text-center text-muted">No administrators found</td></tr>
                        <?php else: ?>
                            <?php foreach ($admins as $admin): ?>
                                <tr>
                                    <td><?php echo $admin['admin_id']; ?></td>
                                    <td><?php echo $admin['user_id']; ?></td>
                                    <td><?php echo htmlspecialchars($admin['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($admin['email']); ?></td>
                                    <td><span class="access-code"><?php echo htmlspecialchars($admin['access_code']); ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    <?php endif; ?>
    
    <div class="text-center mb-4">
        <a href="register.php" class="btn btn-primary">➕ Add New User</a>
        <a href="index.php" class="btn btn-secondary">🏠 Back to Home</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>