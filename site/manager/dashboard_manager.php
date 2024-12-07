<?php
global $connection;
session_start();
include '../db_connect.php';

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Vérifier que l'utilisateur a le rôle de responsable de projet
if (!isset($_SESSION['role_description']) || $_SESSION['role_description'] !== 'Responsable de projet') {
    echo "Accès refusé. Vous n'avez pas les permissions nécessaires.";
    exit();
}

$user_id = $_SESSION['user_id'];

// Récupérer la liste des projets assignés à ce manager
// On suppose que la table 'Projet' possède une colonne 'idResponsable' qui stocke l'ID de l'utilisateur manager
$stmt = $connection->prepare("
    SELECT idProjet, Nom, Date_Debut, Statut
    FROM Projet
    WHERE idResponsable = ?
    ORDER BY Date_Debut DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$projects = [];
while ($row = $result->fetch_assoc()) {
    $projects[] = $row;
}
$stmt->close();
$connection->close();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Tableau de Bord Manager - FactoDB</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- Lien vers Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
    <style>
        body {
            background: #f8f9fa;
        }
        .navbar {
            background-color: #ffffff;
            border-bottom: 1px solid #e2e2e2;
        }
        .navbar .navbar-brand {
            font-weight: bold;
            font-size: 1.2rem;
        }
        .navbar-nav .nav-link {
            color: #555;
        }
        .navbar-nav .nav-link.active {
            font-weight: 600;
            color: #000;
        }
        .content {
            margin-top: 60px;
        }
        .content h1 {
            font-size: 2rem;
            margin-bottom: 20px;
            font-weight: 700;
            color: #333;
        }
        .content p {
            font-size: 1.1rem;
            margin-bottom: 30px;
            color: #555;
        }
        .table thead th {
            font-weight: 600;
            background: #f1f1f1;
        }
        .status-badge {
            font-size: 0.9rem;
            padding: 4px 8px;
            border-radius: 4px;
            color: #fff;
        }
        .status-encours {
            background-color: #ffc107;
        }
        .status-valide {
            background-color: #28a745;
        }
        .status-refuse {
            background-color: #dc3545;
        }
    </style>
</head>
<body>

<!-- Navbar Manager -->
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <a class="navbar-brand" href="dashboard_manager.php">FactoDB</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Basculer la navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <!-- Menu de navigation -->
        <ul class="navbar-nav ml-auto">
            <li class="nav-item active">
                <a class="nav-link" href="dashboard_manager.php">Mes Projets <span class="sr-only">(actuel)</span></a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../logout.php">Se Déconnecter</a>
            </li>
        </ul>
    </div>
</nav>

<div class="container content">
    <h1>Mes Projets</h1>
    <p>Ci-dessous, la liste de tous les projets qui vous sont assignés.</p>

    <table class="table table-striped table-bordered">
        <thead>
        <tr>
            <th>ID Projet</th>
            <th>Nom du Projet</th>
            <th>Date de Début</th>
            <th>Statut</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php if (count($projects) > 0): ?>
            <?php foreach ($projects as $project): ?>
                <?php
                // Déterminer le badge de statut
                $statut = $project['Statut'];
                $badgeClass = 'status-encours'; // Valeur par défaut
                if (strtolower($statut) === 'validé') {
                    $badgeClass = 'status-valide';
                } elseif (strtolower($statut) === 'refusé') {
                    $badgeClass = 'status-refuse';
                }
                ?>
                <tr>
                    <td><?= htmlspecialchars($project['idProjet']) ?></td>
                    <td><?= htmlspecialchars($project['Nom']) ?></td>
                    <td><?= htmlspecialchars($project['Date_Debut']) ?></td>
                    <td><span class="status-badge <?= $badgeClass ?>"><?= htmlspecialchars($statut) ?></span></td>
                    <td>
                        <a href="project.php?id=<?= urlencode($project['idProjet']) ?>" class="btn btn-sm btn-primary">Accéder au projet</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="5" class="text-center">Aucun projet ne vous est assigné pour le moment.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Inclusion de Bootstrap JS et ses dépendances -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
