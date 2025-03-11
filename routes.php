<?php
require_once 'controllers/UserController.php';

$route = $_GET['route'] ?? 'login';

if ($route == 'login') {
    require 'views/login.php';
} else {
    echo "Page non trouvée";
}
?>
