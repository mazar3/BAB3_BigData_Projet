<?php
session_start();

// Vérifier si l'utilisateur est connecté et est un Administrateur
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role_description']) || $_SESSION['role_description'] !== 'Administrateur') {
    header("Location: ../login.php");
    exit();
}

include '../db_connect.php';

$message = '';

// Gestion des mises à jour de statut via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Mise à jour du statut du Panier
    if (isset($_POST['update_panier']) && isset($_POST['idPanier'])) {
        $idPanier = intval($_POST['idPanier']);
        $currentStatut = $_POST['currentStatut'];

        // Déterminer le nouveau statut
        $nouveauStatut = ($currentStatut === 'Validé') ? 'Non validé' : 'Validé';

        // Préparer la requête de mise à jour
        $update_panier_stmt = $connection->prepare("UPDATE Panier SET Statut = ? WHERE idPanier = ?");
        if ($update_panier_stmt) {
            $update_panier_stmt->bind_param("si", $nouveauStatut, $idPanier);
            if ($update_panier_stmt->execute()) {
                $message = "Le statut du panier ID $idPanier a été mis à jour en '$nouveauStatut'.";
            } else {
                $message = "Erreur lors de la mise à jour du statut du panier : " . htmlspecialchars($update_panier_stmt->error);
            }
            $update_panier_stmt->close();
        } else {
            $message = "Erreur de préparation de la requête de mise à jour du panier : " . htmlspecialchars($connection->error);
        }
    }

    // Mise à jour du statut de la Facture
    if (isset($_POST['update_facture']) && isset($_POST['idFacture'])) {
        $idFacture = intval($_POST['idFacture']);
        $currentStatut = $_POST['currentStatutFacture'];

        // Déterminer le nouveau statut
        $nouveauStatut = ($currentStatut === 'Payé') ? 'Impayé' : 'Payé';

        // Préparer la requête de mise à jour
        $update_facture_stmt = $connection->prepare("UPDATE Facture SET Statut = ? WHERE idFacture = ?");
        if ($update_facture_stmt) {
            $update_facture_stmt->bind_param("si", $nouveauStatut, $idFacture);
            if ($update_facture_stmt->execute()) {
                $message = "Le statut de la facture ID $idFacture a été mis à jour en '$nouveauStatut'.";
            } else {
                $message = "Erreur lors de la mise à jour du statut de la facture : " . htmlspecialchars($update_facture_stmt->error);
            }
            $update_facture_stmt->close();
        } else {
            $message = "Erreur de préparation de la requête de mise à jour de la facture : " . htmlspecialchars($connection->error);
        }
    }
}

// Récupérer les informations de l'administrateur pour affichage (optionnel)
$user_id = $_SESSION['user_id'];
$stmt = $connection->prepare("SELECT Nom, Prenom FROM Utilisateur WHERE idUtilisateur = ?");
if (!$stmt) {
    die("Erreur de préparation de la requête utilisateur : " . htmlspecialchars($connection->error));
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($nom, $prenom);
$stmt->fetch();
$stmt->close();

// Récupérer les Paniers
$paniers = [];
$panier_stmt = $connection->prepare("
    SELECT p.idPanier, u.Email, p.Date_Panier, p.Montant, p.Statut
    FROM Panier p
    JOIN Projet pr ON p.idPanier = pr.idPanier
    JOIN Utilisateur u ON pr.idUtilisateur = u.idUtilisateur
    ORDER BY p.Date_Panier DESC
");
if (!$panier_stmt) {
    die("Erreur de préparation de la requête Panier : " . htmlspecialchars($connection->error));
}
$panier_stmt->execute();
$panier_result = $panier_stmt->get_result();
while ($row = $panier_result->fetch_assoc()) {
    $paniers[] = $row;
}
$panier_stmt->close();

// Récupérer les Factures
$factures = [];
$factures_stmt = $connection->prepare("
    SELECT f.idFacture, p.idPanier, u.Email, f.Date_Facture, f.Montant, f.Statut
    FROM Facture f
    JOIN Commande c ON f.idCommande = c.idCommande
    JOIN Utilisateur u ON c.idUtilisateur = u.idUtilisateur
    JOIN Projet pr ON u.idProjet = pr.idProjet
    JOIN Panier p ON pr.idPanier = p.idPanier
    ORDER BY f.Date_Facture DESC
");
if (!$factures_stmt) {
    die("Erreur de préparation de la requête Facture : " . htmlspecialchars($connection->error));
}
$factures_stmt->execute();
$factures_result = $factures_stmt->get_result();
while ($row = $factures_result->fetch_assoc()) {
    $factures[] = $row;
}
$factures_stmt->close();

// Fermer la connexion
$connection->close();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Informations Financières</title>
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
            <li class="nav-item">
                <a class="nav-link" href="dashboard_admin.php">Accueil</a>
            </li>
            <li class="nav-item active">
                <a class="nav-link" href="view_financials.php">Informations Financières <span class="sr-only">(actuel)</span></a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="manage_users.php">Gérer les Utilisateurs</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="manage_projects.php">Gérer les Projets</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../logout.php">Se Déconnecter</a>
            </li>
        </ul>
    </div>
</nav>

<div class="container mt-5">
    <h2>Informations Financières</h2>

    <!-- Affichage des messages de mise à jour -->
    <?php if (!empty($message)): ?>
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($message) ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Fermer">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <!-- Section Paniers -->
    <div class="mt-4">
        <h4>Paniers</h4>
        <table class="table table-striped table-bordered">
            <thead class="thead-dark">
            <tr>
                <th>ID Panier</th>
                <th>Client Email</th>
                <th>Date</th>
                <th>Montant (€)</th>
                <th>Statut</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            <?php if (count($paniers) > 0): ?>
                <?php foreach ($paniers as $p): ?>
                    <tr>
                        <td><?= htmlspecialchars($p['idPanier']) ?></td>
                        <td><?= htmlspecialchars($p['Email']) ?></td>
                        <td><?= htmlspecialchars($p['Date_Panier']) ?></td>
                        <td><?= htmlspecialchars(number_format($p['Montant'], 2, ',', ' ')) ?></td>
                        <td><?= htmlspecialchars($p['Statut']) ?></td>
                        <td>
                            <form method="POST" action="view_financials.php">
                                <input type="hidden" name="idPanier" value="<?= htmlspecialchars($p['idPanier']) ?>">
                                <input type="hidden" name="currentStatut" value="<?= htmlspecialchars($p['Statut']) ?>">
                                <button type="submit" name="update_panier" class="btn btn-sm btn-primary">
                                    <?= ($p['Statut'] === 'Validé') ? 'Marquer Non Validé' : 'Marquer Validé' ?>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center">Aucun panier disponible.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Section Factures -->
    <div class="mt-5">
        <h4>Factures</h4>
        <table class="table table-striped table-bordered">
            <thead class="thead-dark">
            <tr>
                <th>ID Facture</th>
                <th>ID Panier</th>
                <th>Client Email</th>
                <th>Date</th>
                <th>Montant (€)</th>
                <th>Statut Paiement</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            <?php if (count($factures) > 0): ?>
                <?php foreach ($factures as $f): ?>
                    <tr>
                        <td><?= htmlspecialchars($f['idFacture']) ?></td>
                        <td><?= htmlspecialchars($f['idPanier']) ?></td>
                        <td><?= htmlspecialchars($f['Email']) ?></td>
                        <td><?= htmlspecialchars($f['Date_Facture']) ?></td>
                        <td><?= htmlspecialchars(number_format($f['Montant'], 2, ',', ' ')) ?></td>
                        <td><?= htmlspecialchars($f['Statut']) ?></td>
                        <td>
                            <form method="POST" action="view_financials.php">
                                <input type="hidden" name="idFacture" value="<?= htmlspecialchars($f['idFacture']) ?>">
                                <input type="hidden" name="currentStatutFacture" value="<?= htmlspecialchars($f['Statut']) ?>">
                                <button type="submit" name="update_facture" class="btn btn-sm btn-primary">
                                    <?= ($f['Statut'] === 'Payé') ? 'Marquer Impayé' : 'Marquer Payé' ?>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center">Aucune facture disponible.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Inclusion de Bootstrap JS et ses dépendances -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
