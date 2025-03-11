<?php
session_start();
include_once __DIR__ . '/../config/database.php';
include_once __DIR__ . '/../models/Vehicle.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Instancier le modèle Vehicle
$vehicleModel = new Vehicle($pdo);
$vehicles = $vehicleModel->getAllVehicles();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Véhicules disponibles</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <h1>Véhicules disponibles</h1>
    <a href="../index.php">Retour</a>
    <div class="vehicles-container">
        <?php foreach ($vehicles as $vehicle): ?>
            <div class="vehicle-card">
                <h3><?= htmlspecialchars($vehicle['marque'] . " " . $vehicle['modele']) ?></h3>
                <p>Prix: <?= htmlspecialchars($vehicle['prix_par_jour']) ?> €/jour</p>
                <p>Disponibilité: <?= $vehicle['disponible'] ? 'Oui' : 'Non' ?></p>
                <?php if ($vehicle['disponible']): ?>
                    <a href="reservations.php?vehicle_id=<?= $vehicle['id'] ?>" class="btn">Réserver</a>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>