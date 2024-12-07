<?php

global $connection;
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Vérifier si l'utilisateur a le rôle "Client"
if (!isset($_SESSION['role_description']) || $_SESSION['role_description'] !== 'Client') {
    header("Location: ../login.php");
    exit();
}

include '../db_connect.php';
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
    <title>Tableau de Bord Client</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <a class="navbar-brand" href="#">FactoDB</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Basculer la navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ml-auto">
            <li class="nav-item active">
                <a class="nav-link" href="dashboard_client.php">Accueil <span class="sr-only">(actuel)</span></a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="view_projects.php">Consulter mes projets</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="create_projects.php">Créer un projet</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../logout.php">Se Déconnecter</a>
            </li>
        </ul>
    </div>
</nav>

<div class="container mt-5">
    <div class="jumbotron">
        <h1 class="display-4">Bienvenue, <?= htmlspecialchars($prenom . ' ' . $nom); ?>!</h1>
        <p class="lead">Accédez à vos services et fonctionnalités personnalisées ci-dessous.</p>
        <hr class="my-4">
        <p>Vous pouvez consulter nos produits ou créer un projet</p>
        <a class="btn btn-primary btn-lg" href="view_projects.php" role="button">Consulter les Produits</a>
        <a class="btn btn-success btn-lg" href="create_projects.php" role="button">Créer un projet</a>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
