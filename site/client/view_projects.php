<?php
session_start();
include '../db_connect.php'; // Adapter le chemin si nécessaire

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Préparer et exécuter la requête pour récupérer les projets de l'utilisateur connecté
$stmt = $connection->prepare("
    SELECT idProjet, Nom, Date_Debut, Statut
    FROM Projet
    WHERE idUtilisateur = ?
    ORDER BY Date_Debut DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Stocker les projets dans un tableau
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
    <title>Mes Projets - FactoDB</title>
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

<!-- Navbar du client -->
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <a class="navbar-brand" href="dashboard_client.php">FactoDB</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Basculer la navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <!-- Menu de navigation -->
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <a class="nav-link" href="dashboard_client.php">Accueil</a>
            </li>
            <li class="nav-item active">
                <a class="nav-link" href="view_projects.php">Consulter mes projets <span class="sr-only">(actuel)</span></a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="create_projects.php">Créer un nouveau projet</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../logout.php">Se Déconnecter</a>
            </li>
        </ul>
    </div>
</nav>

<div class="container content">
    <h1>Mes Projets</h1>
    <p>Ci-dessous, la liste de tous vos projets en cours, validés, ou en attente de validation.</p>

    <table class="table table-striped table-bordered">
        <thead>
        <tr>
            <th>ID Projet</th>
            <th>Nom du Projet</th>
            <th>Date de Création</th>
            <th>Statut</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php if (count($projects) > 0): ?>
            <?php foreach ($projects as $project): ?>
                <tr>
                    <td><?= htmlspecialchars($project['idProjet']) ?></td>
                    <td><?= htmlspecialchars($project['Nom']) ?></td>
                    <td><?= htmlspecialchars($project['Date_Debut']) ?></td>
                    <td>
                        <?php
                        // Adapter les badges en fonction du statut
                        $statut = $project['Statut'];
                        $badgeClass = '';
                        if (strtolower($statut) === 'en cours de validation') {
                            $badgeClass = 'status-encours';
                        } elseif (strtolower($statut) === 'validé') {
                            $badgeClass = 'status-valide';
                        } elseif (strtolower($statut) === 'refusé') {
                            $badgeClass = 'status-refuse';
                        } else {
                            // Statut inconnu, on peut définir une couleur par défaut
                            $badgeClass = 'status-encours';
                        }
                        ?>
                        <span class="status-badge <?= $badgeClass ?>"><?= htmlspecialchars($statut) ?></span>
                    </td>
                    <td>
                        <a href="project_details.php?id=<?= urlencode($project['idProjet']) ?>" class="btn btn-sm btn-info">Détails</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="5" class="text-center">Aucun projet disponible.</td>
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
