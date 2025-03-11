<?php
require_once _DIR_ . '/../config/database.php';

class Reservation {
    private $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    public function getReservationsByUser($user_id) {
        $stmt = $this->db->prepare("SELECT r.id, v.marque, r.date_debut, r.date_fin 
                                    FROM reservation r
                                    JOIN vehicule v ON r.vehicule_id = v.id
                                    WHERE r.utilisateur_id = ?");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>