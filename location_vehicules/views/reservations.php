<?php
session_start();
include "../config/database.php";
include "../models/Reservation.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../views/login.php");
    exit();
}

$reservationModel = new Reservation($pdo);
$reservations = $reservationModel->getReservationsByUser($_SESSION['user_id']);
?>

<h1>Mes Réservations</h1>
<div class="reservations-container">
    <ul>
        <?php foreach ($reservations as $reservation): ?>
            <li>Véhicule : <?= htmlspecialchars($reservation['marque']) ?> - Date : <?= $reservation['date_debut'] ?> à <?= $reservation['date_fin'] ?></li>
        <?php endforeach; ?>
    </ul>
</div>