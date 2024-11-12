<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tableau de bord</title>
    <!-- Inclusion de Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h1>Bienvenue sur votre tableau de bord</h1>
    <p>Vous êtes connecté en tant que : <?= htmlspecialchars($_SESSION['email']) ?></p>
    <a href="logout.php" class="btn btn-danger">Se déconnecter</a>
</div>
</body>
</html>
