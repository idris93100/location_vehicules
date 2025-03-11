<?php
require_once __DIR__ . '/../models/User.php';

class UserController {
    public function login() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $username = $_POST['username'];
            $password = $_POST['password'];

            $userModel = new User();
            $user = $userModel->login($username, $password);

            if ($user) {
                session_start();
                $_SESSION['user'] = $user;
                header("Location: ../views/dashboard.php");
                exit;
            } else {
                echo "Identifiants incorrects.";
            }
        }
    }
}

if (isset($_GET['action']) && $_GET['action'] == 'login') {
    $controller = new UserController();
    $controller->login();
}
?>
