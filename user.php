<?php
// User.php
class User {
    private $user_id;
    private $role_id;
    private $gender_id;
    private $full_name;
    private $email;
    private $password_hash;
    private $verified;
    private $created_at;
    
    // Additional properties for role and gender names
    private $role_name;
    private $gender_name;

    public function __construct($full_name, $email, $password, $role_id = 1, $gender_id = null, $user_id = null) {
        $this->user_id = $user_id;
        $this->full_name = $full_name;
        $this->email = $email;
        $this->role_id = $role_id;
        $this->gender_id = $gender_id;
        $this->verified = 0;
        
        if (strlen($password) < 60) { // Not already hashed
            $this->setPassword($password);
        } else {
            $this->password_hash = $password; // Already hashed from database
        }
        
        $this->created_at = $this->created_at ?? date('Y-m-d H:i:s');
    }

    // Getters
    public function getUserId() {
        return $this->user_id;
    }

    public function getId() {
        return $this->user_id; // Alias for compatibility
    }

    public function getFullName() {
        return $this->full_name;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getUsername() {
        return $this->full_name; // Use full_name as username
    }

    public function getRoleId() {
        return $this->role_id;
    }

    public function getRoleName() {
        return $this->role_name;
    }

    public function getRole() {
        return $this->role_name; // For compatibility
    }

    public function getGenderId() {
        return $this->gender_id;
    }

    public function getGenderName() {
        return $this->gender_name;
    }

    public function getPasswordHash() {
        return $this->password_hash;
    }

    public function isVerified() {
        return $this->verified == 1;
    }

    public function getCreatedAt() {
        return $this->created_at;
    }

    // Setters
    public function setUserId($user_id) {
        $this->user_id = $user_id;
    }

    public function setId($id) {
        $this->user_id = $id; // Alias for compatibility
    }

    public function setPassword($password) {
        $this->password_hash = password_hash($password, PASSWORD_DEFAULT);
    }

    public function setEmail($email) {
        $this->email = $email;
    }

    public function setFullName($full_name) {
        $this->full_name = $full_name;
    }

    public function setRoleId($role_id) {
        $this->role_id = $role_id;
    }

    public function setRoleName($role_name) {
        $this->role_name = $role_name;
    }

    public function setGenderId($gender_id) {
        $this->gender_id = $gender_id;
    }

    public function setGenderName($gender_name) {
        $this->gender_name = $gender_name;
    }

    public function setVerified($verified) {
        $this->verified = $verified;
    }

    public function setCreatedAt($created_at) {
        $this->created_at = $created_at;
    }

    // Authentication methods
    public function verifyPassword($password) {
        return password_verify($password, $this->password_hash);
    }

    public function isAdmin() {
        return $this->role_name === 'admin';
    }

    public function isOwner() {
        return $this->role_name === 'rehomer' || $this->role_name === 'admin';
    }

    public function isRehomer() {
        return $this->role_name === 'rehomer';
    }

    public function isClient() {
        return $this->role_name === 'client';
    }

    public function isUser() {
        return in_array($this->role_name, ['client', 'rehomer', 'admin']);
    }

    // Convert to array for session storage
    public function toArray() {
        return [
            'user_id' => $this->user_id,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'role_id' => $this->role_id,
            'role_name' => $this->role_name,
            'gender_id' => $this->gender_id,
            'gender_name' => $this->gender_name,
            'verified' => $this->verified,
            'created_at' => $this->created_at
        ];
    }

    // Create User from array (for session data)
    public static function fromArray($data) {
        $user = new User(
            $data['full_name'], 
            $data['email'], 
            '', // Empty password since it's from session
            $data['role_id'], 
            $data['gender_id'] ?? null,
            $data['user_id']
        );
        $user->setRoleName($data['role_name'] ?? '');
        $user->setGenderName($data['gender_name'] ?? '');
        $user->setVerified($data['verified'] ?? 0);
        $user->setCreatedAt($data['created_at']);
        return $user;
    }
}
?>
