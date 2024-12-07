<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>FactoDB - Accueil</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- Lien vers Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
    <style>
        /* Styles additionnels pour personnaliser la page */
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
        .hero {
            padding: 100px 0;
            text-align: center;
            background: #ffffff;
        }
        .hero h1 {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 20px;
            color: #333;
        }
        .hero p {
            font-size: 1.2rem;
            color: #555;
            margin-bottom: 40px;
        }
        .btn-custom {
            font-size: 1.2rem;
            padding: 10px 30px;
        }
    </style>
</head>
<body>

<!-- Barre de navigation -->
<nav class="navbar navbar-expand-lg navbar-light">
    <div class="container">
        <a class="navbar-brand" href="#">FactoDB</a>
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <!-- Lien "Se connecter" en haut à droite -->
                <a class="nav-link" href="login.php">Se connecter</a>
            </li>
        </ul>
    </div>
</nav>

<!-- Section principale (Hero) -->
<div class="hero">
    <div class="container">
        <h1>Bienvenue chez FactoDB</h1>
        <p>
            FactoDB est votre partenaire de confiance pour la gestion de projets,
            le suivi de vos commandes et la gestion de vos données informatiques.
            Notre équipe met en place des solutions sur mesure afin de répondre à
            tous vos besoins, du plus simple au plus complexe.
        </p>
        <!-- Bouton Passer une commande -->
        <a href="login.php" class="btn btn-primary btn-custom">Passer une commande</a>
    </div>
</div>

<!-- Inclusion de Bootstrap JS et dépendances -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
