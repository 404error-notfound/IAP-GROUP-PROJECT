<?php
// Puppy.php
class Puppy {
    private $id;
    private $name;
    private $breed;
    private $age;
    private $vaccinationDate;
    private $location;
    private $adoptionFee;
    private $status; // 'available', 'taken'
    private $ownerId;
    private $createdAt;

    public function __construct($name, $breed, $age, $ownerId) {
        $this->name = $name;
        $this->breed = $breed;
        $this->age = $age;
        $this->ownerId = $ownerId;
        $this->status = 'available';
        $this->createdAt = date('Y-m-d H:i:s');
    }

    // Business logic methods
    public function isAvailable() {
        return $this->status === 'available';
    }

    public function markAsTaken() {
        $this->status = 'taken';
    }
}
