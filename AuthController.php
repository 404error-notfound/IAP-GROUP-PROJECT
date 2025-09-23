<?php
// AuthController.php
require_once 'UserRepository.php';
require_once 'user.php';

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

    public function register($email, $username, $password, $confirmPassword, $role = 'user') {
        $this->errors = [];

        // Validate input
        if (empty($email)) {
            $this->errors[] = "Email is required";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = "Invalid email format";
        }

        if (empty($username)) {
            $this->errors[] = "Username is required";
        } elseif (strlen($username) < 3) {
            $this->errors[] = "Username must be at least 3 characters";
        }

        if (empty($password)) {
            $this->errors[] = "Password is required";
        } elseif (strlen($password) < 6) {
            $this->errors[] = "Password must be at least 6 characters";
        }

        if ($password !== $confirmPassword) {
            $this->errors[] = "Passwords do not match";
        }

        // Check if email/username already exists
        if (empty($this->errors)) {
            if ($this->userRepository->emailExists($email)) {
                $this->errors[] = "Email already exists";
            }
            if ($this->userRepository->usernameExists($username)) {
                $this->errors[] = "Username already exists";
            }
        }

        if (!empty($this->errors)) {
            return false;
        }

        try {
            // Create new user
            $user = new User($email, $password, $role, $username);
            
            if ($this->userRepository->save($user)) {
                $this->messages[] = "Account created successfully! You can now login.";
                return true;
            } else {
                $this->errors[] = "Registration failed. Please try again.";
                return false;
            }
        } catch (Exception $e) {
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