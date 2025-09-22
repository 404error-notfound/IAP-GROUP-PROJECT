<?php
// User.php
class User {
    private $id;
    private $email;
    private $password;
    private $role; // 'user', 'owner', 'admin'
    private $createdAt;

    public function __construct($email, $password, $role = 'user') {
        $this->email = $email;
        $this->setPassword($password);
        $this->role = $role;
        $this->createdAt = date('Y-m-d H:i:s');
    }

    // Getters and setters
    public function setPassword($password) {
        $this->password = password_hash($password, PASSWORD_DEFAULT);
    }

    public function verifyPassword($password) {
        return password_verify($password, $this->password);
    }
}
