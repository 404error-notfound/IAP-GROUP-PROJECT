<?php
// AuthController.php
namespace Angel\IapGroupProject\Controllers;

use Angel\IapGroupProject\Repositories\UserRepository;
use Angel\IapGroupProject\User;
use Angel\IapGroupProject\Services\EmailService;

class AuthController {
    private $userRepository;
    private $errors = [];
    private $messages = [];

    public function __construct() {
        $this->userRepository = new UserRepository();
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function login($identifier, $password) {
        // Clear previous errors
        $this->errors = [];

        // Validate input
        if (empty($identifier)) {
            $this->errors[] = "Email or username is required";
        }

        if (empty($password)) {
            $this->errors[] = "Password is required";
        }

        if (!empty($this->errors)) {
            return false;
        }

        try {
            // Find user by email or username
            $user = $this->userRepository->findByEmailOrUsername($identifier);

            if ($user && $user->verifyPassword($password)) {
                // Check if user is verified
                if (!$user->isVerified()) {
                    $this->errors[] = "Please verify your email address before logging in. Check your email for the verification link.";
                    return false;
                }
                
                // Successful login
                $this->createUserSession($user);
                return true;
            } else {
                $this->errors[] = "Invalid credentials";
                return false;
            }
        } catch (Exception $e) {
            $this->errors[] = "Login failed. Please try again.";
            return false;
        }
    }

    public function logout() {
        // Clear all session data
        session_unset();
        session_destroy();
        
        // Start a new session for flash messages
        session_start();
        $this->messages[] = "You have been logged out successfully.";
    }

    public function isLoggedIn() {
        return isset($_SESSION['user']) && !empty($_SESSION['user']);
    }

    public function getCurrentUser() {
        if ($this->isLoggedIn()) {
            return User::fromArray($_SESSION['user']);
        }
        return null;
    }

    public function requireLogin($redirectUrl = '/login.php') {
        if (!$this->isLoggedIn()) {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            header("Location: $redirectUrl");
            exit;
        }
    }

    public function requireRole($role, $redirectUrl = '/index.php') {
        $this->requireLogin();
        
        $user = $this->getCurrentUser();
        if (!$user) {
            header("Location: /login.php");
            exit;
        }

        $hasPermission = false;
        switch ($role) {
            case 'admin':
                $hasPermission = $user->isAdmin();
                break;
            case 'owner':
                $hasPermission = $user->isOwner();
                break;
            case 'user':
                $hasPermission = $user->isUser();
                break;
        }

        if (!$hasPermission) {
            $this->errors[] = "Access denied. Insufficient permissions.";
            header("Location: $redirectUrl");
            exit;
        }
    }

    private function createUserSession(User $user) {
        // Regenerate session ID for security
        session_regenerate_id(true);
        
        // Store user data in session
        $_SESSION['user'] = $user->toArray();
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();
    }

    public function register($email, $full_name, $password, $confirmPassword, $account_type = 'client', $gender = null, $phone = null, $preferred_breed = null, $preferred_age = null, $license_number = null, $location = null, $contact_email_1 = null, $contact_email_2 = null) {
        $this->errors = [];

        // Validate input
        if (empty($email)) {
            $this->errors[] = "Email is required";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = "Invalid email format";
        }

        if (empty($full_name)) {
            $this->errors[] = "Full name is required";
        } elseif (strlen($full_name) < 2) {
            $this->errors[] = "Full name must be at least 2 characters";
        }

        if (empty($password)) {
            $this->errors[] = "Password is required";
        } elseif (strlen($password) < 6) {
            $this->errors[] = "Password must be at least 6 characters";
        }

        if ($password !== $confirmPassword) {
            $this->errors[] = "Passwords do not match";
        }

        // Check if email already exists
        if (empty($this->errors)) {
            if ($this->userRepository->emailExists($email)) {
                $this->errors[] = "Email already exists";
            }
        }

        // Validate account type specific fields
        if ($account_type === 'rehomer') {
            if (empty($license_number)) {
                $this->errors[] = "License number is required for rehomers";
            }
            if (empty($location)) {
                $this->errors[] = "Location is required for rehomers";
            }
        }

        if (!empty($this->errors)) {
            return false;
        }

        try {
            // Get role ID and gender ID
            $role_id = $this->userRepository->getRoleIdByName($account_type);
            $gender_id = $gender ? $this->userRepository->getGenderIdByName($gender) : null;

            // Create new user
            $user = new User($full_name, $email, $password, $role_id, $gender_id);
            
            if ($this->userRepository->save($user)) {
                // Save additional data based on account type
                if ($account_type === 'client') {
                    // Prepare dog preferences
                    $dog_preferences = [];
                    if (is_array($preferred_breed) && !empty($preferred_breed)) {
                        $dog_preferences['breeds'] = $preferred_breed;
                    } elseif (is_string($preferred_breed) && !empty($preferred_breed)) {
                        $dog_preferences['breeds'] = [$preferred_breed];
                    }
                    if ($preferred_age) {
                        $dog_preferences['age'] = $preferred_age;
                    }
                    $preferences_json = !empty($dog_preferences) ? json_encode($dog_preferences) : null;
                    
                    $this->userRepository->saveClientData($user->getUserId(), $preferences_json);
                    
                } elseif ($account_type === 'rehomer') {
                    // Use contact_email_1 as the primary contact email
                    $contact_email = $contact_email_1 ?: $email;
                    $this->userRepository->saveRehomerData(
                        $user->getUserId(), 
                        $license_number, 
                        $location, 
                        $contact_email
                    );
                } elseif ($account_type === 'admin') {
                    // Generate and save admin access code
                    $emailService = new EmailService();
                    $adminAccessCode = $emailService->generateAdminAccessCode();
                    $this->userRepository->saveAdminData($user->getUserId(), $adminAccessCode);
                }
                
                // Generate verification token and send email
                $emailService = new EmailService();
                $verificationToken = $emailService->generateVerificationToken();
                $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));
                
                // Save verification token
                $this->userRepository->saveVerificationToken($user->getUserId(), $verificationToken, $expiresAt);
                
                // Send verification email
                $isAdmin = ($account_type === 'admin');
                $adminAccessCode = null;
                
                if ($isAdmin) {
                    // Get the admin access code for email
                    $adminData = $this->userRepository->getAdminByAccessCode($adminAccessCode ?? '');
                    if (!$adminData) {
                        // Fallback: regenerate and get the code
                        $emailServiceTemp = new EmailService();
                        $adminAccessCode = $emailServiceTemp->generateAdminAccessCode();
                        $this->userRepository->saveAdminData($user->getUserId(), $adminAccessCode);
                    } else {
                        $adminAccessCode = $adminData['access_code'];
                    }
                }
                
                if ($emailService->sendVerificationEmail($email, $full_name, $verificationToken, $isAdmin, $adminAccessCode)) {
                    $this->messages[] = "🎉 Registration successful! A verification email has been sent to " . $email;
                    $this->messages[] = "� Please check your email inbox (and spam folder) and click the verification link to activate your account.";
                    if ($isAdmin) {
                        $this->messages[] = "🔑 As an administrator, you will receive a unique access code in your email that must be used for login.";
                    }
                } else {
                    $this->messages[] = "❌ Account created successfully, but there was an issue sending the verification email. Please check your email configuration or contact support.";
                }
                
                return true;
            } else {
                $this->errors[] = "Registration failed. Please try again.";
                return false;
            }
        } catch (Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            $this->errors[] = "Registration failed. Please try again.";
            return false;
        }
    }

    public function getErrors() {
        return $this->errors;
    }

    public function getMessages() {
        return $this->messages;
    }

    public function getLastError() {
        return end($this->errors);
    }

    public function getLastMessage() {
        return end($this->messages);
    }

    // Check session timeout (30 minutes)
    public function checkSessionTimeout($timeoutMinutes = 30) {
        if (isset($_SESSION['last_activity'])) {
            $inactive = time() - $_SESSION['last_activity'];
            
            if ($inactive >= ($timeoutMinutes * 60)) {
                $this->logout();
                $this->errors[] = "Session expired. Please login again.";
                return false;
            }
        }
        
        $_SESSION['last_activity'] = time();
        return true;
    }
}
?>