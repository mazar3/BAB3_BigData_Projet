<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$role_description = $_SESSION['role_description'];

switch ($role_description) {
    case 'Administrateur':
        header("Location: admin_dashboard.php");
        break;
    case 'Responsable de projet':
        header("Location: manager_dashboard.php");
        break;
    case 'Collaborateur':
        header("Location: collaborator_dashboard.php");
        break;
    case 'Client':
        header("Location: client_dashboard.php");
        break;
    default:
        echo "Rôle non reconnu.";
        session_destroy();
        exit();
}
?>