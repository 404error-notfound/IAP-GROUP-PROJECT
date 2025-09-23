<?php
// UserRepository.php
require_once 'Database.php';
require_once 'user.php';

class UserRepository {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function findByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($userData) {
            return new User(
                $userData['email'], 
                $userData['password'], 
                $userData['role'], 
                $userData['username'] ?? $userData['email'],
                $userData['id']
            );
        }
        return null;
    }

    public function findByUsername($username) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($userData) {
            return new User(
                $userData['email'], 
                $userData['password'], 
                $userData['role'], 
                $userData['username'],
                $userData['id']
            );
        }
        return null;
    }

    public function findByEmailOrUsername($identifier) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ? OR username = ?");
        $stmt->execute([$identifier, $identifier]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($userData) {
            return new User(
                $userData['email'], 
                $userData['password'], 
                $userData['role'], 
                $userData['username'] ?? $userData['email'],
                $userData['id']
            );
        }
        return null;
    }

    public function save(User $user) {
        if ($user->getId()) {
            // Update existing user
            $stmt = $this->db->prepare("
                UPDATE users 
                SET email = ?, username = ?, password = ?, role = ? 
                WHERE id = ?
            ");
            return $stmt->execute([
                $user->getEmail(),
                $user->getUsername(),
                $user->getPassword(),
                $user->getRole(),
                $user->getId()
            ]);
        } else {
            // Create new user
            $stmt = $this->db->prepare("
                INSERT INTO users (email, username, password, role, created_at) 
                VALUES (?, ?, ?, ?, NOW())
            ");
            $success = $stmt->execute([
                $user->getEmail(),
                $user->getUsername(),
                $user->getPassword(),
                $user->getRole()
            ]);
            
            if ($success) {
                $user->setId($this->db->lastInsertId());
            }
            
            return $success;
        }
    }

    public function emailExists($email) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetchColumn() > 0;
    }

    public function usernameExists($username) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetchColumn() > 0;
    }
}
?>