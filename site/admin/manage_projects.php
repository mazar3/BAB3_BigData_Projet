<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gérer les Projets - FactoDB</title>
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
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <a class="navbar-brand" href="dashboard_admin.php">FactoDB</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Basculer la navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <a class="nav-link" href="dashboard_admin.php">Accueil</a>
            </li>
            <li class="nav-item active">
                <a class="nav-link" href="manage_projects.php">Gérer les Projets <span class="sr-only">(actuel)</span></a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="manage_users.php">Gérer les Utilisateurs</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="view_financials.php">Voir les Informations Financières</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../logout.php">Se Déconnecter</a>
            </li>
        </ul>
    </div>
</nav>

<div class="container content">
    <h1>Gérer les Projets</h1>
    <p>Liste des projets en cours. Vous pouvez affecter un projet à un ou plusieurs responsables.</p>

    <table class="table table-striped table-bordered">
        <thead>
        <tr>
            <th>ID Projet</th>
            <th>Nom du Projet</th>
            <th>Date de Début</th>
            <th>Date de Fin</th>
            <th>Statut</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php
        // Connexion à la base de données
        $conn = new mysqli("localhost", "root", "", "bab3_bigdata_projet");
        if ($conn->connect_error) {
            die("Erreur de connexion : " . $conn->connect_error);
        }

        // Requête pour récupérer les projets en cours
        $sql = "SELECT idProjet, Nom, Date_Debut, Date_Fin, Statut FROM Projet WHERE Statut = 'En cours'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                            <td>" . htmlspecialchars($row['idProjet']) . "</td>
                            <td>" . htmlspecialchars($row['Nom']) . "</td>
                            <td>" . htmlspecialchars($row['Date_Debut']) . "</td>
                            <td>" . htmlspecialchars($row['Date_Fin']) . "</td>
                            <td>" . htmlspecialchars($row['Statut']) . "</td>
                            <td>
                                <a href='assign_manager.php?id=" . urlencode($row['idProjet']) . "' class='btn btn-sm btn-primary'>Affecter Responsable</a>
                            </td>
                          </tr>";
            }
        } else {
            echo "<tr><td colspan='6' class='text-center'>Aucun projet en cours.</td></tr>";
        }

        $conn->close();
        ?>
        </tbody>
    </table>
</div>

<!-- Inclusion de Bootstrap JS et ses dépendances -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
