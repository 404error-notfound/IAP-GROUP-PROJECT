<?php
session_start();

require_once __DIR__ . '/../../bootstrap.php';

use Angel\IapGroupProject\Database;

// Check if user is logged in as rehomer
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'rehomer' && $_SESSION['user_role'] !== 'rehomer')) {
    $_SESSION['error'] = "You must be logged in as a rehomer to add dogs.";
    header("Location: ../login.php");
    exit();
}

// Get rehomer_id from session
$rehomer_id = $_SESSION['rehomer_id'] ?? null;

if (!$rehomer_id) {
    $_SESSION['error'] = "Rehomer ID not found. Please contact support.";
    header("Location: ../rehomer/rehomer-dashboard.php");
    exit();
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: AddPuppy.php");
    exit();
}

try {
    $db = Database::getInstance()->getConnection();
    
    // Validate and sanitize inputs
    $name = trim($_POST['name'] ?? '');
    $breed_name = trim($_POST['breed'] ?? '');
    $age = (int)($_POST['age'] ?? 0);
    $gender = trim($_POST['gender'] ?? '');
    $adoption_fee = isset($_POST['adoption_fee']) ? (float)$_POST['adoption_fee'] : 0.00;
    $description = trim($_POST['description'] ?? '');
    
    // Validation
    $errors = [];
    
    if (empty($name)) {
        $errors[] = "Dog name is required.";
    }
    
    if (empty($breed_name)) {
        $errors[] = "Breed is required.";
    }
    
    if ($age <= 0) {
        $errors[] = "Valid age is required.";
    }
    
    if (empty($gender)) {
        $errors[] = "Gender is required.";
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
    
    error_log("Error adding dog: " . $e->getMessage());
    $_SESSION['error'] = "An error occurred while adding the dog. Please try again.";
    header("Location: AddPuppy.php");
    exit();
} catch (Exception $e) {
    error_log("Unexpected error: " . $e->getMessage());
    $_SESSION['error'] = "An unexpected error occurred. Please try again.";
    header("Location: AddPuppy.php");
    exit();
}
