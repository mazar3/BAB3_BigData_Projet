<?php
// header.php
session_start();

// Fonction pour vérifier si l'utilisateur est connecté
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Fonction pour obtenir le rôle de l'utilisateur
function get_role() {
    return isset($_SESSION['role_description']) ? $_SESSION['role_description'] : '';
}

// Fonction pour générer le lien du tableau de bord en fonction du rôle
function get_dashboard_link() {
    switch (get_role()) {
        case 'Administrateur':
            return '../admin/dashboard_admin.php';
        case 'Responsable de projet':
            return '../manager/dashboard_manager.php';
        case 'Client':
            return '../client/dashboard_client.php';
        default:
            return '../login.php';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>FactoDB</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS (version 4.5.2) -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <!-- DataTables Responsive CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">

    <!-- Lien vers un fichier CSS personnalisé -->
    <link rel="stylesheet" href="styles.css">
    <!-- jQuery (déjà inclus) -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>

    <!-- Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>

    <!-- Bootstrap JS -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <!-- DataTables Responsive JS -->
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>


    <!-- Styles additionnels pour personnaliser la page -->
    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            padding-top: 50px;
            background: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .navbar-brand {
            font-weight: bold;
            font-size: 1.5rem;
        }
        .navbar-nav .nav-link {
            font-size: 1rem;
        }
        .nav-link.active {
            font-weight: bold;
            color: #007bff !important;
        }
        .footer {
            background-color: #343a40;
            color: #ffffff;
            padding: 20px 0;
            border-top: 1px solid #e2e2e2;
            margin-top: 50px;
        }
        .footer a {
            color: #ffffff;
            text-decoration: none;
        }
        .footer a:hover {
            text-decoration: underline;
        }
        .dropdown-item.active, .dropdown-item:active {
            background-color: #007bff;
            color: #ffffff;
        }
    </style>
</head>
<body>

<!-- Barre de navigation -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <div class="container">
        <a class="navbar-brand" href="../dashboard.php">FactoDB</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Basculer la navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <?php if (is_logged_in()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= htmlspecialchars(get_dashboard_link()); ?>">Tableau de Bord</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../logout.php">Se Déconnecter</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="../login.php">Se connecter</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- Conteneur principal -->
<main class="flex-fill">
    <div class="container mt-4">
        <!-- Le contenu spécifique à chaque page sera inséré ici -->
