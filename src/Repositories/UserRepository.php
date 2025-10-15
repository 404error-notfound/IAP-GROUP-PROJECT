<?php
// UserRepository.php
namespace Angel\IapGroupProject\Repositories;

use Angel\IapGroupProject\Database;
use Angel\IapGroupProject\User;

class UserRepository {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function findByEmail($email) {
        $stmt = $this->db->prepare("
            SELECT u.*, ur.role_name, ug.gender_name 
            FROM users u 
            LEFT JOIN user_roles ur ON u.role_id = ur.role_id 
            LEFT JOIN user_gender ug ON u.gender_id = ug.gender_id 
            WHERE u.email = ?
        ");
        $stmt->execute([$email]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($userData) {
            $user = new User(
                $userData['full_name'], 
                $userData['email'], 
                $userData['password_hash'], 
                $userData['role_id'],
                $userData['gender_id'],
                $userData['user_id']
            );
            $user->setRoleName($userData['role_name']);
            $user->setGenderName($userData['gender_name']);
            $user->setVerified($userData['verified']);
            $user->setCreatedAt($userData['created_at']);
            return $user;
        }
        return null;
    }

    public function findByEmailOrUsername($identifier) {
        // Since we use full_name instead of username, search by email or full_name
        $stmt = $this->db->prepare("
            SELECT u.*, ur.role_name, ug.gender_name 
            FROM users u 
            LEFT JOIN user_roles ur ON u.role_id = ur.role_id 
            LEFT JOIN user_gender ug ON u.gender_id = ug.gender_id 
            WHERE u.email = ? OR u.full_name = ?
        ");
        $stmt->execute([$identifier, $identifier]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($userData) {
            $user = new User(
                $userData['full_name'], 
                $userData['email'], 
                $userData['password_hash'], 
                $userData['role_id'],
                $userData['gender_id'],
                $userData['user_id']
            );
            $user->setRoleName($userData['role_name']);
            $user->setGenderName($userData['gender_name']);
            $user->setVerified($userData['verified']);
            $user->setCreatedAt($userData['created_at']);
            return $user;
        }
        return null;
    }

    public function save(User $user) {
        if ($user->getUserId()) {
            // Update existing user
            $stmt = $this->db->prepare("
                UPDATE users 
                SET full_name = ?, email = ?, password_hash = ?, role_id = ?, gender_id = ?
                WHERE user_id = ?
            ");
            return $stmt->execute([
                $user->getFullName(),
                $user->getEmail(),
                $user->getPasswordHash(),
                $user->getRoleId(),
                $user->getGenderId(),
                $user->getUserId()
            ]);
        } else {
            // Create new user
            $stmt = $this->db->prepare("
                INSERT INTO users (role_id, gender_id, full_name, email, password_hash, verified) 
                VALUES (?, ?, ?, ?, ?, 0)
            ");
            $success = $stmt->execute([
                $user->getRoleId(),
                $user->getGenderId(),
                $user->getFullName(),
                $user->getEmail(),
                $user->getPasswordHash()
            ]);
            
            if ($success) {
                $user->setUserId($this->db->lastInsertId());
            }
            
            return $success;
        }
    }

    public function emailExists($email) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetchColumn() > 0;
    }

    public function fullNameExists($full_name) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE full_name = ?");
        $stmt->execute([$full_name]);
        return $stmt->fetchColumn() > 0;
    }

    // Get role ID by role name
    public function getRoleIdByName($role_name) {
        $stmt = $this->db->prepare("SELECT role_id FROM user_roles WHERE role_name = ?");
        $stmt->execute([$role_name]);
        $result = $stmt->fetchColumn();
        return $result ? $result : 1; // Default to client (1) if not found
    }

    // Get gender ID by gender name
    public function getGenderIdByName($gender_name) {
        $stmt = $this->db->prepare("SELECT gender_id FROM user_gender WHERE gender_name = ?");
        $stmt->execute([ucfirst(strtolower($gender_name))]);
        return $stmt->fetchColumn();
    }

    // Get all roles
    public function getAllRoles() {
        $stmt = $this->db->query("SELECT * FROM user_roles");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get all genders
    public function getAllGenders() {
        $stmt = $this->db->query("SELECT * FROM user_gender");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get all breeds
    public function getAllBreeds() {
        $stmt = $this->db->query("SELECT * FROM breeds ORDER BY breed_name");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Save client-specific data
    public function saveClientData($user_id, $dog_preferences) {
        $stmt = $this->db->prepare("
            INSERT INTO clients (user_id, dog_preferences) 
            VALUES (?, ?)
            ON DUPLICATE KEY UPDATE dog_preferences = VALUES(dog_preferences)
        ");
        return $stmt->execute([$user_id, $dog_preferences]);
    }

    // Save rehomer-specific data
    public function saveRehomerData($user_id, $license_number, $location, $contact_email) {
        $stmt = $this->db->prepare("
            INSERT INTO rehomers (user_id, license_number, location, contact_email) 
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
                license_number = VALUES(license_number),
                location = VALUES(location),
                contact_email = VALUES(contact_email)
        ");
        return $stmt->execute([$user_id, $license_number, $location, $contact_email]);
    }
}
?>