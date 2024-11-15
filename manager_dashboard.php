<?php
global $connection;
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Vérifier si l'utilisateur a le rôle "Responsable de projet"
if (!isset($_SESSION['role_description']) || $_SESSION['role_description'] !== 'Responsable de projet') {
    header("Location: login.php");
    exit();
}

include 'db_connect.php';
$user_id = $_SESSION['user_id'];
$stmt = $connection->prepare("SELECT Nom, Prenom FROM utilisateur WHERE idUtilisateur = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($nom, $prenom);
$stmt->fetch();
$stmt->close();
$connection->close();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Tableau de Bord Responsable de Projet</title>
    <!-- Inclusion de Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
    <!-- Optionnel : Inclure un favicon ou d'autres métadonnées -->
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <a class="navbar-brand" href="#">FactoDB</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Basculer la navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <!-- Menu de navigation -->
        <ul class="navbar-nav ml-auto">
            <li class="nav-item active">
                <a class="nav-link" href="manager_dashboard.php">Accueil <span class="sr-only">(actuel)</span></a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="view_projects.php">Voir Mes Projets</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="modify_orders.php">Modifier les Commandes</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="assign_collaborators.php">Assigner des Collaborateurs</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="logout.php">Se Déconnecter</a>
            </li>
        </ul>
    </div>
</nav>

<div class="container mt-5">
    <!-- Bienvenue à l'utilisateur -->
    <div class="jumbotron">
        <h1 class="display-4">Bienvenue, <?= htmlspecialchars($prenom . ' ' . $nom); ?>!</h1>
        <p class="lead">Accédez à vos projets et commandes spécifiques ci-dessous.</p>
        <hr class="my-4">
        <p>Vous pouvez consulter vos projets, modifier les commandes en cours, et assigner des collaborateurs.</p>
        <a class="btn btn-primary btn-lg" href="view_projects.php" role="button">Voir Mes Projets</a>
        <a class="btn btn-warning btn-lg" href="modify_orders.php" role="button">Modifier les Commandes</a>
        <a class="btn btn-info btn-lg" href="assign_collaborators.php" role="button">Assigner des Collaborateurs</a>
    </div>
</div>

<!-- Inclusion de Bootstrap JS et ses dépendances -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>