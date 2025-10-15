<?php
// PuppyRepository.php
class PuppyRepository {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function save(Puppy $puppy) {
        $stmt = $this->db->prepare("INSERT INTO puppies (name, breed, age, vaccination_date, location, adoption_fee, owner_id, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$puppy->getName(), $puppy->getBreed(), $puppy->getAge(), $puppy->getVaccinationDate(), $puppy->getLocation(), $puppy->getAdoptionFee(), $puppy->getOwnerId(), $puppy->getStatus()]);
        
        return $this->db->lastInsertId();
    }

    public function findAvailablePuppies($filters = []) {
        $sql = "SELECT * FROM puppies WHERE status = 'available'";
        $params = [];

        // Dynamic filtering based on criteria
        if (!empty($filters['breed'])) {
            $sql .= " AND breed = ?";
            $params[] = $filters['breed'];
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
