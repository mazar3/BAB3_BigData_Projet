<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role_description'] !== 'Responsable de projet') {
    header("Location: login.php");
    exit();
}

// Code pour assigner des collaborateurs
?>