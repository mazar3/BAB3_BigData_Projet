<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Créer un Nouveau Projet - FactoDB</title>
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
            <li class="nav-item">
                <a class="nav-link" href="view_projects.php">Consulter mes projets</a>
            </li>
            <li class="nav-item active">
                <a class="nav-link" href="create_projects.php">Créer un nouveau projet <span class="sr-only">(actuel)</span></a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../logout.php">Se Déconnecter</a>
            </li>
        </ul>
    </div>
</nav>

<div class="container content">
    <h1>Créer un Nouveau Projet</h1>
    <p>Veuillez décrire votre projet afin que nous puissions l'analyser et le valider. Soyez aussi précis que possible pour faciliter la compréhension de vos besoins.</p>

    <form method="post" action="create_projet.php">
        <div class="form-group">
            <label for="descriptionProjet">Description du Projet</label>
            <textarea class="form-control" id="descriptionProjet" name="descriptionProjet" rows="8" placeholder="Décrivez votre projet en détail..."></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Créer le projet</button>
    </form>
</div>

<!-- Inclusion de Bootstrap JS et ses dépendances -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
