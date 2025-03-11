<?php
session_start();
require_once "config/database.php";

// Connexion à la base de données
$database = new Database();
$conn = $database->getConnection();

// Gestion de l'inscription
if (isset($_POST['register'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = "client";

    $query = "INSERT INTO users (username, password, role) VALUES (:username, :password, :role)";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(":username", $username);
    $stmt->bindParam(":password", $password);
    $stmt->bindParam(":role", $role);

    if ($stmt->execute()) {
        echo "Inscription réussie ! Vous pouvez maintenant vous connecter.";
    } else {
        echo "Erreur lors de l'inscription.";
    }
}

// Gestion de la connexion
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE username = :username";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(":username", $username);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['username'] = $user['username'];
    } else {
        echo "Identifiants incorrects.";
    }
}

// Gestion des véhicules
if (isset($_POST['add_vehicle']) && $_SESSION['role'] == 'admin') {
    $marque = $_POST['marque'];
    $prix_journalier = $_POST['prix_journalier'];
    $couleur = $_POST['couleur'];

    $query = "INSERT INTO vehicles (marque, prix_journalier, couleur) VALUES (:marque, :prix_journalier, :couleur)";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(":marque", $marque);
    $stmt->bindParam(":prix_journalier", $prix_journalier);
    $stmt->bindParam(":couleur", $couleur);

    if ($stmt->execute()) {
        echo "Véhicule ajouté avec succès.";
    } else {
        echo "Erreur lors de l'ajout du véhicule.";
    }
}

// Gestion des réservations
if (isset($_POST['reserve_vehicle']) && isset($_SESSION['user_id'])) {
    $vehicle_id = $_POST['vehicle_id'];
    $user_id = $_SESSION['user_id'];
    $date_debut = $_POST['date_debut'];
    $date_fin = $_POST['date_fin'];

    $query = "INSERT INTO reservations (user_id, vehicle_id, date_debut, date_fin) VALUES (:user_id, :vehicle_id, :date_debut, :date_fin)";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(":user_id", $user_id);
    $stmt->bindParam(":vehicle_id", $vehicle_id);
    $stmt->bindParam(":date_debut", $date_debut);
    $stmt->bindParam(":date_fin", $date_fin);

    if ($stmt->execute()) {
        echo "Réservation effectuée avec succès.";
    } else {
        echo "Erreur lors de la réservation.";
    }
}

// Déconnexion
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Location de Véhicules</title>
    <link rel="stylesheet" href="public/styles.css">
</head>
<body>
    <h1>Bienvenue sur la plateforme de location de véhicules</h1>

    <?php if (!isset($_SESSION['user_id'])): ?>
        <h2>Inscription</h2>
        <form method="post">
            <input type="text" name="username" placeholder="Nom d'utilisateur" required>
            <input type="password" name="password" placeholder="Mot de passe" required>
            <button type="submit" name="register">S'inscrire</button>
        </form>

        <h2>Connexion</h2>
        <form method="post">
            <input type="text" name="username" placeholder="Nom d'utilisateur" required>
            <input type="password" name="password" placeholder="Mot de passe" required>
            <button type="submit" name="login">Se connecter</button>
        </form>
    <?php else: ?>
        <p>Connecté en tant que <strong><?php echo $_SESSION['username']; ?></strong> (<?php echo $_SESSION['role']; ?>)</p>
        <a href="index.php?logout=true">Se déconnecter</a>

        <?php if ($_SESSION['role'] == 'admin'): ?>
            <h2>Ajouter un véhicule</h2>
            <form method="post">
                <input type="text" name="marque" placeholder="Marque" required>
                <input type="number" name="prix_journalier" placeholder="Prix journalier" required>
                <input type="text" name="couleur" placeholder="Couleur" required>
                <button type="submit" name="add_vehicle">Ajouter</button>
            </form>
        <?php endif; ?>

        <h2><a href="views/vehicles.php">Véhicules disponibles</a></h2>
        <ul>
            <?php
            $query = "SELECT * FROM vehicles";
            $stmt = $conn->prepare($query);
            $stmt->execute();
            $vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($vehicles as $vehicle):
            ?>
                <li>
                    <?php echo $vehicle['marque']; ?> - 
                    <?php echo $vehicle['prix_journalier']; ?>€/jour - 
                    <?php echo $vehicle['couleur']; ?>
                    <?php if ($_SESSION['role'] == 'client'): ?>
                        <form method="post">
                            <input type="hidden" name="vehicle_id" value="<?php echo $vehicle['id']; ?>">
                            <label>Date début:</label>
                            <input type="date" name="date_debut" required>
                            <label>Date fin:</label>
                            <input type="date" name="date_fin" required>
                            <button type="submit" name="reserve_vehicle">Réserver</button>
                        </form>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>

        <h2><a href="views/reservations.php">Mes réservations</a></h2>
        <ul>
            <?php
            $query = "SELECT vehicles.marque, reservations.date_debut, reservations.date_fin 
                      FROM reservations 
                      JOIN vehicles ON reservations.vehicle_id = vehicles.id 
                      WHERE reservations.user_id = :user_id";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(":user_id", $_SESSION['user_id']);
            $stmt->execute();
            $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($reservations as $res):
            ?>
                <li><?php echo $res['marque']; ?> : du <?php echo $res['date_debut']; ?> au <?php echo $res['date_fin']; ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</body>
</html>