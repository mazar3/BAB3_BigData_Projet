<?php
// admin/dashboard_admin.php
global $connection;
include '../header.php';
include '../db_connect.php';

// Vérifier si l'utilisateur est connecté et a le rôle "Administrateur"
if (!isset($_SESSION['user_id']) || get_role() !== 'Administrateur') {
    header("Location: ../login.php");
    exit();
}

// Récupérer les statistiques pour le tableau de bord

// 1. Nombre total d'utilisateurs
$stmt = $connection->prepare("SELECT COUNT(*) FROM Utilisateur");
$stmt->execute();
$stmt->bind_result($total_users);
$stmt->fetch();
$stmt->close();

// 2. Nombre total de projets
$stmt = $connection->prepare("SELECT COUNT(*) FROM Projet");
$stmt->execute();
$stmt->bind_result($total_projects);
$stmt->fetch();
$stmt->close();

// 3. Utilisateurs par rôle
$stmt = $connection->prepare("
    SELECT r.Description, COUNT(u.idUtilisateur) 
    FROM Utilisateur u
    JOIN Role r ON u.idRole = r.idRole
    GROUP BY r.Description
");
$stmt->execute();
$result = $stmt->get_result();
$users_by_role = [];
while ($row = $result->fetch_assoc()) {
    $users_by_role[$row['Description']] = $row['COUNT(u.idUtilisateur)'];
}
$stmt->close();

// 4. Projets par statut
$stmt = $connection->prepare("
    SELECT p.Statut, COUNT(p.idProjet) 
    FROM Projet p
    GROUP BY p.Statut
");
$stmt->execute();
$result = $stmt->get_result();
$projects_by_status = [];
while ($row = $result->fetch_assoc()) {
    $projects_by_status[$row['Statut']] = $row['COUNT(p.idProjet)'];
}
$stmt->close();

// 5. Nombre total de produits
$stmt = $connection->prepare("SELECT COUNT(*) FROM Produit");
$stmt->execute();
$stmt->bind_result($total_products);
$stmt->fetch();
$stmt->close();

// 6. Produits par type
$stmt = $connection->prepare("
    SELECT tp.Nom, COUNT(p.idProduit) 
    FROM Produit p
    LEFT JOIN Type_produit tp ON p.idTypeProduit = tp.idTypeProduit
    GROUP BY tp.Nom
");
$stmt->execute();
$result = $stmt->get_result();
$products_by_type = [];
while ($row = $result->fetch_assoc()) {
    $products_by_type[$row['Nom'] ?? 'Non Spécifié'] = $row['COUNT(p.idProduit)'];
}
$stmt->close();

// 7. Produits par fournisseur
$stmt = $connection->prepare("
    SELECT f.Nom, COUNT(p.idProduit) 
    FROM Produit p
    LEFT JOIN Fournisseur f ON p.idFournisseur = f.idFournisseur
    GROUP BY f.Nom
");
$stmt->execute();
$result = $stmt->get_result();
$products_by_supplier = [];
while ($row = $result->fetch_assoc()) {
    $products_by_supplier[$row['Nom'] ?? 'Non Spécifié'] = $row['COUNT(p.idProduit)'];
}
$stmt->close();

// 8. Stock total
$stmt = $connection->prepare("SELECT SUM(Stock) FROM Produit");
$stmt->execute();
$stmt->bind_result($total_stock);
$stmt->fetch();
$stmt->close();
$total_stock = $total_stock ?? 0;

// 9. Produits en rupture de stock (stock < 10)
$stmt = $connection->prepare("SELECT COUNT(*) FROM Produit WHERE Stock < 10");
$stmt->execute();
$stmt->bind_result($low_stock_products);
$stmt->fetch();
$stmt->close();

// Fermer la connexion
$connection->close();
?>

<div class="container mt-5">
    <h1 class="mb-4">Tableau de Bord Administrateur</h1>

    <div class="row">
        <!-- Carte des utilisateurs -->
        <div class="col-md-4">
            <div class="card text-white bg-primary mb-3">
                <div class="card-header">Total Utilisateurs</div>
                <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($total_users) ?></h5>
                </div>
            </div>
        </div>

        <!-- Carte des projets -->
        <div class="col-md-4">
            <div class="card text-white bg-success mb-3">
                <div class="card-header">Total Projets</div>
                <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($total_projects) ?></h5>
                </div>
            </div>
        </div>

        <!-- Carte des produits -->
        <div class="col-md-4">
            <div class="card text-white bg-info mb-3">
                <div class="card-header">Total Produits</div>
                <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($total_products) ?></h5>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Carte du stock total -->
        <div class="col-md-6">
            <div class="card text-white bg-warning mb-3">
                <div class="card-header">Stock Total</div>
                <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($total_stock) ?> unités</h5>
                </div>
            </div>
        </div>

        <!-- Carte des produits en rupture de stock -->
        <div class="col-md-6">
            <div class="card text-white bg-danger mb-3">
                <div class="card-header">Produits en Rupture de Stock</div>
                <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($low_stock_products) ?></h5>
                </div>
            </div>
        </div>
    </div>

    <!-- Section des utilisateurs par rôle -->
    <div class="row">
        <div class="col-md-6">
            <h3>Utilisateurs par Rôle</h3>
            <ul class="list-group">
                <?php foreach ($users_by_role as $role => $count): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <?= htmlspecialchars($role) ?>
                        <span class="badge badge-primary badge-pill"><?= htmlspecialchars($count) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Section des projets par statut -->
        <div class="col-md-6">
            <h3>Projets par Statut</h3>
            <ul class="list-group">
                <?php foreach ($projects_by_status as $status => $count): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <?= htmlspecialchars($status) ?>
                        <span class="badge badge-success badge-pill"><?= htmlspecialchars($count) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <!-- Section des produits par type -->
    <div class="row mt-4">
        <div class="col-md-6">
            <h3>Produits par Type</h3>
            <ul class="list-group">
                <?php foreach ($products_by_type as $type => $count): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <?= htmlspecialchars($type) ?>
                        <span class="badge badge-info badge-pill"><?= htmlspecialchars($count) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Section des produits par fournisseur -->
        <div class="col-md-6">
            <h3>Produits par Fournisseur</h3>
            <ul class="list-group">
                <?php foreach ($products_by_supplier as $fournisseur => $count): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <?= htmlspecialchars($fournisseur) ?>
                        <span class="badge badge-secondary badge-pill"><?= htmlspecialchars($count) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <!-- Navigation vers les sections d'administration -->
    <div class="mt-5">
        <h3>Gestion</h3>
        <div class="list-group">
            <a href="manage_users.php" class="list-group-item list-group-item-action">Gestion des Utilisateurs</a>
            <a href="manage_projects.php" class="list-group-item list-group-item-action">Gestion des Projets</a>
            <a href="manage_products.php" class="list-group-item list-group-item-action">Gestion des Produits</a>
            <!-- Ajoutez d'autres liens de gestion selon vos besoins -->
        </div>
    </div>
</div>

<?php include '../footer.php'; ?>
