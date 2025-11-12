<?php
require_once __DIR__ . '/../../bootstrap.php';

use Angel\IapGroupProject\Database;

// Check if user is logged in as rehomer
$userRole = $_SESSION['role'] ?? $_SESSION['user_role'] ?? null;
if (!isset($_SESSION['user_id']) || $userRole !== 'rehomer') {
    $_SESSION['error'] = "You must be logged in as a rehomer to add dogs.";
    header("Location: ../login.php");
    exit();
}

// Get rehomer_id from session or database
$rehomer_id = $_SESSION['rehomer_id'] ?? null;

if (!$rehomer_id) {
    // Try to fetch rehomer_id from database using user_id
    try {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT rehomer_id FROM rehomers WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $rehomer_data = $stmt->fetch();
        
        if ($rehomer_data) {
            $rehomer_id = $rehomer_data['rehomer_id'];
            // Store it in session for future use
            $_SESSION['rehomer_id'] = $rehomer_id;
        } else {
            $_SESSION['error'] = "Rehomer profile not found. Please contact support.";
            header("Location: ../rehomer/rehomer-dashboard.php");
            exit();
        }
    } catch (PDOException $e) {
        error_log("Error fetching rehomer_id: " . $e->getMessage());
        $_SESSION['error'] = "Database error. Please try again.";
        header("Location: ../rehomer/rehomer-dashboard.php");
        exit();
    }
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Form not submitted via POST";
    header("Location: AddPuppy.php");
    exit();
}

// Check if POST size exceeded limit
$contentLength = $_SERVER['CONTENT_LENGTH'] ?? 0;
$postMaxSize = ini_get('post_max_size');
$postMaxBytes = parse_size($postMaxSize);

function parse_size($size) {
    $unit = strtoupper(substr($size, -1));
    $value = intval($size);
    switch($unit) {
        case 'G': return $value * 1024 * 1024 * 1024;
        case 'M': return $value * 1024 * 1024;
        case 'K': return $value * 1024;
        default: return $value;
    }
}

if ($contentLength > $postMaxBytes) {
    $sizeMB = round($contentLength / (1024 * 1024), 2);
    $limitMB = round($postMaxBytes / (1024 * 1024), 2);
    $_SESSION['error'] = "Upload too large! Your file is {$sizeMB} MB but the limit is {$limitMB} MB. Please select a smaller image (max 5 MB recommended).";
    header("Location: AddPuppy.php");
    exit();
}

// Debug: Log all request information
error_log("=== DEBUG START ===");
error_log("REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD']);
error_log("CONTENT_TYPE: " . ($_SERVER['CONTENT_TYPE'] ?? 'NOT SET'));
error_log("CONTENT_LENGTH: " . $contentLength);
error_log("POST data: " . print_r($_POST, true));
error_log("FILES data: " . print_r($_FILES, true));
error_log("=== DEBUG END ===");

// Debug: Check if POST data exists
if (empty($_POST)) {
    $_SESSION['error'] = "No POST data received. Debug info logged to error log. REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD'];
    header("Location: AddPuppy.php");
    exit();
}

// Log the submission
error_log("Dog submission received from user_id: " . $_SESSION['user_id']);
error_log("POST data: " . print_r($_POST, true));

try {
    $db = Database::getInstance()->getConnection();
    
    // Validate and sanitize inputs
    $name = trim($_POST['name'] ?? '');
    $breed_name = trim($_POST['breed'] ?? '');
    $age = (int)($_POST['age'] ?? 0);
    $gender = trim($_POST['gender'] ?? '');
    $adoption_fee = isset($_POST['adoption_fee']) ? (float)$_POST['adoption_fee'] : 0.00;
    $description = trim($_POST['description'] ?? '');
    
    // Debug log
    error_log("Parsed values - Name: '$name', Breed: '$breed_name', Age: $age, Gender: '$gender'");
    
    // Validation
    $errors = [];
    
    if (empty($name)) {
        $errors[] = "Dog name is required. (Received: '" . ($_POST['name'] ?? 'MISSING') . "')";
    }
    
    if (empty($breed_name)) {
        $errors[] = "Breed is required. (Received: '" . ($_POST['breed'] ?? 'MISSING') . "')";
    }
    
    if ($age <= 0) {
        $errors[] = "Valid age is required. (Received: '" . ($_POST['age'] ?? 'MISSING') . "')";
    }
    
    if (empty($gender)) {
        $errors[] = "Gender is required. (Received: '" . ($_POST['gender'] ?? 'MISSING') . "')";
    }
    
    // Handle image upload
    $image_url = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        
        if (!in_array($file_extension, $allowed_extensions)) {
            $errors[] = "Invalid image format. Only JPG, JPEG, PNG, and GIF are allowed.";
        } else {
            // Create uploads directory if it doesn't exist
            $upload_dir = __DIR__ . '/../images/dogs/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            // Generate unique filename
            $unique_filename = uniqid('dog_', true) . '.' . $file_extension;
            $upload_path = $upload_dir . $unique_filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $image_url = '/images/dogs/' . $unique_filename;
            } else {
                $errors[] = "Failed to upload image.";
            }
        }
    }
    
    // If there are validation errors, redirect back with errors
    if (!empty($errors)) {
        $_SESSION['error'] = implode('<br>', $errors);
        header("Location: AddPuppy.php");
        exit();
    }
    
    // Start transaction
    $db->beginTransaction();
    
    // Get or insert breed
    $stmt = $db->prepare("SELECT breed_id FROM breeds WHERE breed_name = ?");
    $stmt->execute([$breed_name]);
    $breed = $stmt->fetch();
    
    if ($breed) {
        $breed_id = $breed['breed_id'];
    } else {
        // Insert new breed
        $stmt = $db->prepare("INSERT INTO breeds (breed_name) VALUES (?)");
        $stmt->execute([$breed_name]);
        $breed_id = $db->lastInsertId();
    }
    
    // Get dog_gender_id
    $stmt = $db->prepare("SELECT dog_gender_id FROM dog_gender WHERE gender_name = ?");
    $stmt->execute([$gender]);
    $dog_gender = $stmt->fetch();
    
    if (!$dog_gender) {
        // Insert new gender if it doesn't exist
        $stmt = $db->prepare("INSERT INTO dog_gender (gender_name) VALUES (?)");
        $stmt->execute([$gender]);
        $dog_gender_id = $db->lastInsertId();
    } else {
        $dog_gender_id = $dog_gender['dog_gender_id'];
    }
    
    // Insert dog
    $stmt = $db->prepare("
        INSERT INTO dogs (rehomer_id, breed_id, dog_gender_id, name, age, adoption_fee, image_url, description, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Available')
    ");
    
    $stmt->execute([
        $rehomer_id,
        $breed_id,
        $dog_gender_id,
        $name,
        $age,
        $adoption_fee,
        $image_url,
        $description
    ]);
    
    $dog_id = $db->lastInsertId();
    
    // Commit transaction
    $db->commit();
    
    $_SESSION['success'] = "Dog '{$name}' has been successfully added!";
    header("Location: ../rehomer/rehomer-dashboard.php");
    exit();
    
} catch (PDOException $e) {
    // Rollback on error
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    
    $errorMsg = "Database error: " . $e->getMessage();
    error_log($errorMsg);
    error_log("POST data: " . print_r($_POST, true));
    error_log("Rehomer ID: " . ($rehomer_id ?? 'NULL'));
    
    $_SESSION['error'] = "Database error: " . $e->getMessage();
    header("Location: AddPuppy.php");
    exit();
} catch (Exception $e) {
    $errorMsg = "Unexpected error: " . $e->getMessage();
    error_log($errorMsg);
    error_log("POST data: " . print_r($_POST, true));
    
    $_SESSION['error'] = "Error: " . $e->getMessage();
    header("Location: AddPuppy.php");
    exit();
}
