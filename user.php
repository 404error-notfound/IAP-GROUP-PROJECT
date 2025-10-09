<?php
// User.php
class User {
    private $id;
    private $email;
    private $username;
    private $password;
    private $role; // 'user', 'owner', 'admin'
    private $createdAt;

    public function __construct($email, $password, $role = 'user', $username = null, $id = null) {
        $this->id = $id;
        $this->email = $email;
        $this->username = $username ?? $email;
        if (strlen($password) < 60) { // Not already hashed
            $this->setPassword($password);
        } else {
            $this->password = $password; // Already hashed from database
        }
        $this->role = $role;
        $this->createdAt = $this->createdAt ?? date('Y-m-d H:i:s');
    }

    // Getters
    public function getId() {
        return $this->id;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getUsername() {
        return $this->username;
    }

    public function getRole() {
        return $this->role;
    }

    public function getCreatedAt() {
        return $this->createdAt;
    }

    // Setters
    public function setId($id) {
        $this->id = $id;
    }

    public function setPassword($password) {
        $this->password = password_hash($password, PASSWORD_DEFAULT);
    }

    public function setEmail($email) {
        $this->email = $email;
    }

    public function setUsername($username) {
        $this->username = $username;
    }

    public function setRole($role) {
        $this->role = $role;
    }

    // Authentication methods
    public function verifyPassword($password) {
        return password_verify($password, $this->password);
    }

    public function isAdmin() {
        return $this->role === 'admin';
    }

    public function isOwner() {
        return $this->role === 'owner' || $this->role === 'admin';
    }

    public function isUser() {
        return $this->role === 'user' || $this->role === 'owner' || $this->role === 'admin';
    }

    // Convert to array for session storage
    public function toArray() {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'username' => $this->username,
            'role' => $this->role,
            'created_at' => $this->createdAt
        ];
    }

    // Create User from array (for session data)
    public static function fromArray($data) {
        $user = new User($data['email'], '', $data['role'], $data['username'], $data['id']);
        $user->createdAt = $data['created_at'];
        return $user;
    }
}
