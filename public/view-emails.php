<?php
// view-emails.php - Development tool to view logged emails
require_once __DIR__ . '/../bootstrap.php';

$emailsDir = __DIR__ . '/../logs/emails';
$emailFiles = [];

// Check if emails directory exists and get all email files
if (file_exists($emailsDir)) {
    $files = scandir($emailsDir);
    foreach ($files as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'html') {
            $emailFiles[] = [
                'filename' => $file,
                'timestamp' => filemtime($emailsDir . '/' . $file),
                'size' => filesize($emailsDir . '/' . $file)
            ];
        }
    }
    
    // Sort by newest first
    usort($emailFiles, function($a, $b) {
        return $b['timestamp'] - $a['timestamp'];
    });
}

// Handle email viewing
$viewEmail = $_GET['email'] ?? null;
$emailContent = null;

if ($viewEmail && file_exists($emailsDir . '/' . $viewEmail)) {
    $emailContent = file_get_contents($emailsDir . '/' . $viewEmail);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Development Email Viewer - Go Puppy Go</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            margin: 0;
            padding: 20px;
            background: #f8f9fa;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header {
            background: linear-gradient(135deg, #4472c4 0%, #365a96 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .dev-notice {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .email-list {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .email-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .email-item:hover {
            background: #f8f9fa;
            border-color: #4472c4;
        }
        
        .email-info h4 {
            margin: 0 0 5px 0;
            color: #333;
        }
        
        .email-meta {
            font-size: 14px;
            color: #666;
        }
        
        .email-actions {
            display: flex;
            gap: 10px;
        }
        
        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
            display: inline-block;
        }
        
        .btn-primary {
            background: #4472c4;
            color: white;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        
        .email-viewer {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .viewer-header {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .email-frame {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            width: 100%;
            min-height: 500px;
        }
        
        .no-emails {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #4472c4;
            text-decoration: none;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📧 Development Email Viewer</h1>
            <p>View emails that were logged instead of being sent during development</p>
        </div>
        
        <div class="dev-notice">
            <strong>⚠️ Development Mode Notice:</strong> 
            This page shows emails that were logged to the server instead of being sent to real email addresses. 
            In production, users would receive these emails in their actual inbox.
        </div>

        <?php if ($viewEmail && $emailContent): ?>
            <a href="view-emails.php" class="back-link">← Back to Email List</a>
            
            <div class="email-viewer">
                <div class="viewer-header">
                    <h3>📧 Viewing Email: <?php echo htmlspecialchars($viewEmail); ?></h3>
                    <p><strong>File:</strong> /logs/emails/<?php echo htmlspecialchars($viewEmail); ?></p>
                </div>
                
                <iframe class="email-frame" srcdoc="<?php echo htmlspecialchars($emailContent); ?>"></iframe>
            </div>
            
        <?php else: ?>
            <div class="email-list">
                <h2>📬 Logged Emails (<?php echo count($emailFiles); ?>)</h2>
                
                <?php if (empty($emailFiles)): ?>
                    <div class="no-emails">
                        <h3>📭 No Emails Found</h3>
                        <p>No verification emails have been logged yet.</p>
                        <p>Try registering a new user to generate verification emails.</p>
                        <a href="register.php" class="btn btn-primary">Register New User</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($emailFiles as $email): ?>
                        <div class="email-item" onclick="window.location.href='?email=<?php echo urlencode($email['filename']); ?>'">
                            <div class="email-info">
                                <h4>📧 <?php echo htmlspecialchars($email['filename']); ?></h4>
                                <div class="email-meta">
                                    <strong>Date:</strong> <?php echo date('M j, Y g:i A', $email['timestamp']); ?> | 
                                    <strong>Size:</strong> <?php echo number_format($email['size']); ?> bytes
                                </div>
                            </div>
                            <div class="email-actions">
                                <a href="?email=<?php echo urlencode($email['filename']); ?>" class="btn btn-primary" onclick="event.stopPropagation();">
                                    View Email
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <div style="text-align: center; margin-top: 20px;">
            <a href="register.php" class="btn btn-primary">➕ Register New User</a>
            <a href="admin-dashboard.php" class="btn btn-secondary">👑 Admin Dashboard</a>
            <a href="index.php" class="btn btn-secondary">🏠 Home</a>
        </div>
    </div>
</body>
</html>