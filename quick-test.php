<?php
require_once __DIR__ . '/bootstrap.php';

use Angel\IapGroupProject\Controllers\AuthController;
use Angel\IapGroupProject\Repositories\UserRepository;

// Simple login test
echo "<h1>Simple Login Test</h1>";

$email = 'omondiangela757@gmail.com';
$password = 'password123'; // Change this to your actual password

echo "<h2>Testing login for: " . htmlspecialchars($email) . "</h2>";

try {
    // Test 1: UserRepository
    echo "<h3>1. Testing UserRepository directly:</h3>";
    $userRepo = new UserRepository();
    $user = $userRepo->findByEmailOrUsername($email);
    
    if ($user) {
        echo "✅ User found: " . $user->getFullName() . "<br>";
        echo "✅ Role: " . $user->getRoleName() . "<br>";
        echo "✅ Verified: " . ($user->isVerified() ? 'Yes' : 'No') . "<br>";
    } else {
        echo "❌ User not found<br>";
    }
    
    // Test 2: AuthController
    echo "<h3>2. Testing AuthController:</h3>";
    $auth = new AuthController();
    
    // Try login with placeholder password
    echo "<p><strong>Note:</strong> Enter your actual password below to test login</p>";
    
    echo "<form method='POST'>";
    echo "<input type='password' name='test_password' placeholder='Enter your password' required style='padding: 8px; margin-right: 10px;'>";
    echo "<button type='submit' style='padding: 8px 15px; background: #007bff; color: white; border: none; border-radius: 3px;'>Test Login</button>";
    echo "</form>";
    
    if (isset($_POST['test_password'])) {
        $testPassword = $_POST['test_password'];
        echo "<h4>Testing login with provided password...</h4>";
        
        $loginResult = $auth->login($email, $testPassword);
        
        if ($loginResult) {
            echo "✅ <strong>LOGIN SUCCESSFUL!</strong><br>";
            $currentUser = $auth->getCurrentUser();
            if ($currentUser) {
                echo "User role: " . $currentUser->getRoleName() . "<br>";
                echo "<p>You should now be able to login normally!</p>";
                echo "<p><a href='public/login.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Login Page</a></p>";
            }
        } else {
            echo "❌ <strong>LOGIN FAILED</strong><br>";
            $errors = $auth->getErrors();
            if ($errors) {
                echo "Errors:<br>";
                foreach ($errors as $error) {
                    echo "- " . htmlspecialchars($error) . "<br>";
                }
            }
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>Stack trace: " . $e->getTraceAsString() . "</p>";
}
?>