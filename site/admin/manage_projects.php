<?php
global $connection;
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role_description']) || $_SESSION['role_description'] !== 'Administrateur') {
    header("Location: ../login.php");
    exit();
}
include '../db_connect.php';
$message = '';

// Ajouter un nouveau produit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $Nom = htmlspecialchars($_POST['nom']);
    $Description = htmlspecialchars($_POST['description']);
    $Prix = floatval($_POST['prix']);
    $Stock = intval($_POST['stock']);

    $stmt = $connection->prepare("INSERT INTO Produit (Nom, Description, Prix, Stock) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssdi", $Nom, $Description, $Prix, $Stock);

    if ($stmt->execute()) {
        $message = "<div class='alert alert-success'>Produit ajouté avec succès.</div>";
    } else {
        $message = "<div class='alert alert-danger'>Erreur : " . htmlspecialchars($stmt->error) . "</div>";
    }
    $stmt->close();
}

// Mise à jour du stock
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_stock'])) {
    $idProduit = intval($_POST['idProduit']);
    $Stock = intval($_POST['new_stock']);

    $stmt = $connection->prepare("UPDATE Produit SET Stock = ? WHERE idProduit = ?");
    $stmt->bind_param("ii", $Stock, $idProduit);

    if ($stmt->execute()) {
        $message = "<div class='alert alert-success'>Stock mis à jour avec succès.</div>";
    } else {
        $message = "<div class='alert alert-danger'>Erreur : " . htmlspecialchars($stmt->error) . "</div>";
    }
    $stmt->close();
}

// Récupération de la liste des produits
$produits = [];
$result = $connection->query("SELECT idProduit, Nom, Description, Prix, Stock FROM Produit ORDER BY Nom ASC");
while ($row = $result->fetch_assoc()) {
    $produits[] = $row;
}

// Fermeture de la connexion
$connection->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Produits</title>
    <!-- Inclusion de Bootstrap CSS -->
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
        <!-- Menu de navigation -->
        <ul class="navbar-nav ml-auto">
            <li class="nav-item active">
                <a class="nav-link" href="dashboard.php">Accueil</a>
            </li>
            <li class="nav-item">
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

<div class="container mt-5">
    <h2>Ajouter un Nouveau Produit</h2>
    <?= $message ?>
    <form method="POST" action="">
        <div class="form-group">
            <label for="nom">Nom du produit :</label>
            <input type="text" id="nom" name="nom" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="description">Description :</label>
            <textarea id="description" name="description" class="form-control" required></textarea>
        </div>
        <div class="form-group">
            <label for="prix">Prix :</label>
            <input type="number" id="prix" name="prix" class="form-control" required step="0.01">
        </div>
        <div class="form-group">
            <label for="stock">Stock initial :</label>
            <input type="number" id="stock" name="stock" class="form-control" required min="0">
        </div>
        <button type="submit" name="add_product" class="btn btn-primary">Ajouter le Produit</button>
    </form>

    <h2 class="mt-5">Liste des Produits</h2>
    <table class="table table-striped table-bordered mt-3">
        <thead class="thead-dark">
        <tr>
            <th>Nom</th>
            <th>Description</th>
            <th>Prix (€)</th>
            <th>Stock</th>
            <th>Modifier le Stock</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($produits as $Produit): ?>
            <tr>
                <td><?= htmlspecialchars($Produit['Nom']) ?></td>
                <td><?= htmlspecialchars($Produit['Description']) ?></td>
                <td><?= number_format($Produit['Prix'], 2) ?></td>
                <td><?= $Produit['Stock'] ?></td>
                <td>
                    <form method="POST" action="" class="form-inline">
                        <input type="hidden" name="idProduit" value="<?= $Produit['idProduit'] ?>">
                        <input type="number" name="new_stock" class="form-control mr-2" required min="0" value="<?= $Produit['Stock'] ?>">
                        <button type="submit" name="update_stock" class="btn btn-sm btn-warning">Mettre à jour</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Inclusion de Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
