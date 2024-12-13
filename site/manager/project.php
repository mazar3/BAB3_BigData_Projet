<?php
// admin/project.php
global $connection;
include '../header.php';
include '../db_connect.php';

// Vérifier si l'utilisateur est connecté et a le rôle "Responsable de projet"
if (!isset($_SESSION['user_id']) || get_role() !== 'Responsable de projet') {
    header("Location: ../login.php");
    exit();
}
$manager_id = $_SESSION['user_id'];

// Vérifier si l'ID du projet est fourni
if (!isset($_GET['idProjet']) || empty($_GET['idProjet'])) {
    header("Location: dashboard_manager.php");
    exit();
}

$project_id = intval($_GET['idProjet']);

// Vérifier que le manager est assigné à ce projet
$stmt = $connection->prepare("
    SELECT p.idProjet, p.Nom, p.Description, p.Statut, p.Date_Debut, p.idPanier
    FROM Projet p
    JOIN Projet_manager pm ON p.idProjet = pm.idProjet
    WHERE p.idProjet = ? AND pm.idUtilisateur = ?
");

$stmt->bind_param("ii", $project_id, $manager_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    // Projet non trouvé ou non assigné au manager
    $stmt->close();
    echo '<div class="container mt-5"><div class="alert alert-danger">Projet non trouvé ou vous n\'êtes pas autorisé à y accéder.</div></div>';
    include '../footer.php';
    exit();
}
$project = $result->fetch_assoc();
$stmt->close();

// Initialiser le panier si non existant
$panier_id = $project['idPanier'];
if (is_null($panier_id)) {
    // Créer un nouveau panier
    $stmt = $connection->prepare("INSERT INTO Panier (Date_Panier, Montant, Statut) VALUES (CURDATE(), 0, 'En cours')");
    if ($stmt->execute()) {
        $panier_id = $stmt->insert_id;
        // Mettre à jour le projet avec le nouveau panier
        $stmt_update = $connection->prepare("UPDATE Projet SET idPanier = ? WHERE idProjet = ?");
        $stmt_update->bind_param("ii", $panier_id, $project_id);
        $stmt_update->execute();
        $stmt_update->close();
    }
    $stmt->close();
}

// Traitement du formulaire de modification du projet
$edit_message = '';
$edit_success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    // Vérifier le jeton CSRF si implémenté (voir section Sécurité)
    /*
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die('Erreur de validation du formulaire.');
    }
    */

    if ($_POST['action'] === 'edit_project') {
        // Modification des détails du projet
        $new_nom = trim($_POST['nom']);
        $new_description = trim($_POST['description']);
        $new_statut = trim($_POST['statut']);
        $new_date_debut = trim($_POST['date_debut']);

        // Validation des champs
        if (empty($new_nom) || empty($new_description) || empty($new_statut) || empty($new_date_debut)) {
            $edit_message = "Veuillez remplir tous les champs obligatoires.";
        } elseif (!DateTime::createFromFormat('Y-m-d', $new_date_debut)) {
            $edit_message = "La date de début n'est pas valide.";
        } else {
            // Mettre à jour le projet
            $stmt = $connection->prepare("
                UPDATE Projet 
                SET Nom = ?, Description = ?, Statut = ?, Date_Debut = ?
                WHERE idProjet = ?
            ");
            $stmt->bind_param("ssssi", $new_nom, $new_description, $new_statut, $new_date_debut, $project_id);
            if ($stmt->execute()) {
                $edit_message = "Projet mis à jour avec succès.";
                $edit_success = true;
                // Mettre à jour les variables locales
                $project['Nom'] = $new_nom;
                $project['Description'] = $new_description;
                $project['Statut'] = $new_statut;
                $project['Date_Debut'] = $new_date_debut;
            } else {
                $edit_message = "Erreur lors de la mise à jour du projet : " . htmlspecialchars($stmt->error);
            }
            $stmt->close();
        }
    } elseif ($_POST['action'] === 'add_to_panier') {
        // Ajout de produits au panier
        $product_id = intval($_POST['product_id']);
        $quantite = intval($_POST['quantite']);

        if ($quantite <= 0) {
            $edit_message = "La quantité doit être supérieure à zéro.";
        } else {
            // Vérifier si le produit existe
            $stmt = $connection->prepare("SELECT Stock FROM Produit WHERE idProduit = ?");
            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            $stmt->bind_result($stock);
            if ($stmt->fetch()) {
                if ($quantite > $stock) {
                    $edit_message = "La quantité demandée dépasse le stock disponible.";
                } else {
                    $stmt->close();
                    // Vérifier si le produit est déjà dans le panier
                    $stmt = $connection->prepare("SELECT Quantite FROM Panier_produit WHERE idPanier = ? AND idProduit = ?");
                    $stmt->bind_param("ii", $panier_id, $product_id);
                    $stmt->execute();
                    $stmt->bind_result($existing_quantite);
                    if ($stmt->fetch()) {
                        // Mettre à jour la quantité
                        $new_quantite = $existing_quantite + $quantite;
                        $stmt->close();
                        $stmt = $connection->prepare("UPDATE Panier_produit SET Quantite = ? WHERE idPanier = ? AND idProduit = ?");
                        $stmt->bind_param("iii", $new_quantite, $panier_id, $product_id);
                        if ($stmt->execute()) {
                            $edit_message = "Quantité mise à jour dans le panier.";
                        } else {
                            $edit_message = "Erreur lors de la mise à jour du panier : " . htmlspecialchars($stmt->error);
                        }
                        $stmt->close();
                    } else {
                        // Insérer une nouvelle entrée
                        $stmt->close();
                        $stmt = $connection->prepare("INSERT INTO Panier_produit (idPanier, idProduit, Quantite) VALUES (?, ?, ?)");
                        $stmt->bind_param("iii", $panier_id, $product_id, $quantite);
                        if ($stmt->execute()) {
                            $edit_message = "Produit ajouté au panier.";
                        } else {
                            $edit_message = "Erreur lors de l'ajout au panier : " . htmlspecialchars($stmt->error);
                        }
                        $stmt->close();
                    }
                }
            } else {
                $edit_message = "Produit non trouvé.";
                $stmt->close();
            }
        }
    } elseif ($_POST['action'] === 'validate_devis') {
        // Valider et envoyer le devis au client
        // Mettre à jour le statut du panier et du projet
        $stmt = $connection->prepare("UPDATE Panier SET Statut = 'Devis Envoyé' WHERE idPanier = ?");
        $stmt->bind_param("i", $panier_id);
        if ($stmt->execute()) {
            $stmt->close();
            // Mettre à jour le statut du projet
            $stmt = $connection->prepare("UPDATE Projet SET Statut = 'Devis Envoyé' WHERE idProjet = ?");
            $stmt->bind_param("i", $project_id);
            if ($stmt->execute()) {
                $edit_message = "Devis validé et envoyé au client avec succès.";
                $edit_success = true;
                $project['Statut'] = 'Devis Envoyé';
            } else {
                $edit_message = "Erreur lors de la mise à jour du projet : " . htmlspecialchars($stmt->error);
            }
            $stmt->close();
        } else {
            $edit_message = "Erreur lors de la mise à jour du panier : " . htmlspecialchars($stmt->error);
            $stmt->close();
        }
    }
}

// Récupérer les commentaires du client pour ce projet
$stmt = $connection->prepare("
    SELECT c.Commentaire, c.Date_Commentaire, u.Nom, u.Prenom
    FROM Commentaires c
    JOIN Utilisateur u ON c.idUtilisateur = u.idUtilisateur
    WHERE c.idProjet = ?
    ORDER BY c.Date_Commentaire ASC
");
$stmt->bind_param("i", $project_id);
$stmt->execute();
$result = $stmt->get_result();
$comments = [];
while ($row = $result->fetch_assoc()) {
    $comments[] = $row;
}
$stmt->close();

// Récupérer le contenu du panier
$stmt = $connection->prepare("
    SELECT p.idProduit, p.Nom, p.Prix, pp.Quantite
    FROM Panier_produit pp
    JOIN Produit p ON pp.idProduit = p.idProduit
    WHERE pp.idPanier = ?
");
$stmt->bind_param("i", $panier_id);
$stmt->execute();
$result = $stmt->get_result();
$panier = [];
$total_montant = 0;
while ($row = $result->fetch_assoc()) {
    $panier[] = $row;
    $total_montant += $row['Prix'] * $row['Quantite'];
}
$stmt->close();

// Récupérer tous les produits pour l'ajout au panier
$stmt = $connection->prepare("
    SELECT idProduit, Nom, Description, Prix, Stock
    FROM Produit
    ORDER BY Nom ASC
");
$stmt->execute();
$result = $stmt->get_result();
$all_products = [];
while ($row = $result->fetch_assoc()) {
    $all_products[] = $row;
}
$stmt->close();

$connection->close();
?>

<div class="container mt-5">
    <h1 class="mb-4">Gestion du Projet</h1>

    <!-- Messages de confirmation -->
    <?php if ($edit_message): ?>
        <div class="alert <?= $edit_success ? 'alert-success' : 'alert-danger' ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($edit_message) ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Fermer">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <!-- Formulaire de modification du projet -->
    <div class="card mb-4">
        <div class="card-header">
            <h3>Modifier les Détails du Projet</h3>
        </div>
        <div class="card-body">
            <form method="post" action="">
                <input type="hidden" name="action" value="edit_project">
                <!--
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                -->
                <div class="form-group">
                    <label for="nom">Nom du Projet</label>
                    <input type="text" class="form-control" id="nom" name="nom" value="<?= htmlspecialchars($project['Nom']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="statut">Statut du Projet</label>
                    <select class="form-control" id="statut" name="statut" required>
                        <option value="En cours" <?= ($project['Statut'] === 'En cours') ? 'selected' : '' ?>>En cours</option>
                        <option value="Devis Envoyé" <?= ($project['Statut'] === 'Devis Envoyé') ? 'selected' : '' ?>>Devis Envoyé</option>
                        <option value="Terminé" <?= ($project['Statut'] === 'Terminé') ? 'selected' : '' ?>>Terminé</option>
                        <!-- Ajoutez d'autres statuts si nécessaire -->
                    </select>
                </div>
                <div class="form-group">
                    <label for="date_debut">Date de Début</label>
                    <input type="date" class="form-control" id="date_debut" name="date_debut" value="<?= htmlspecialchars($project['Date_Debut']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="description">Description du Projet</label>
                    <textarea class="form-control" id="description" name="description" rows="5" required><?= htmlspecialchars($project['Description']) ?></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Mettre à Jour le Projet</button>
            </form>
        </div>
    </div>

    <!-- Description et commentaires du client -->
    <div class="card mb-4">
        <div class="card-header">
            <h3>Description et Commentaires du Client</h3>
        </div>
        <div class="card-body">
            <h5>Description :</h5>
            <p><?= nl2br(htmlspecialchars($project['Description'])) ?></p>

            <h5>Commentaires :</h5>
            <?php if (count($comments) === 0): ?>
                <p>Aucun commentaire.</p>
            <?php else: ?>
                <ul class="list-group">
                    <?php foreach ($comments as $comment): ?>
                        <li class="list-group-item">
                            <strong><?= htmlspecialchars($comment['Prenom'] . ' ' . $comment['Nom']) ?></strong> (<?= htmlspecialchars($comment['Date_Commentaire']) ?>) :
                            <p><?= nl2br(htmlspecialchars($comment['Commentaire'])) ?></p>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>

    <!-- Panier des produits -->
    <div class="card mb-4">
        <div class="card-header">
            <h3>Panier</h3>
        </div>
        <div class="card-body">
            <?php if (count($panier) === 0): ?>
                <p>Aucun produit ajouté au panier.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table id="panierTable" class="table table-striped table-bordered">
                        <thead class="thead-dark">
                            <tr>
                                <th>Nom du Produit</th>
                                <th>Prix (€)</th>
                                <th>Quantité</th>
                                <th>Total (€)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($panier as $item): ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['Nom']) ?></td>
                                    <td><?= htmlspecialchars(number_format($item['Prix'], 2, ',', ' ')) ?></td>
                                    <td><?= htmlspecialchars($item['Quantite']) ?></td>
                                    <td><?= htmlspecialchars(number_format($item['Prix'] * $item['Quantite'], 2, ',', ' ')) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <tr>
                                <td colspan="3" class="text-right"><strong>Montant Total :</strong></td>
                                <td><strong><?= htmlspecialchars(number_format($total_montant, 2, ',', ' ')) ?> €</strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <form method="post" action="">
                    <input type="hidden" name="action" value="validate_devis">
                    <!--
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                    -->
                    <button type="submit" class="btn btn-success">Valider et Envoyer le Devis au Client</button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- Liste de tous les produits avec option d'ajout au panier -->
    <div class="card mb-4">
        <div class="card-header">
            <h3>Ajouter des Produits au Projet</h3>
        </div>
        <div class="card-body">
            <?php if (count($all_products) === 0): ?>
                <p>Aucun produit disponible.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table id="allProductsTable" class="table table-striped table-bordered">
                        <thead class="thead-dark">
                            <tr>
                                <th>Nom</th>
                                <th>Description</th>
                                <th>Prix (€)</th>
                                <th>Stock</th>
                                <th>Quantité</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_products as $prod): ?>
                                <tr>
                                    <td><?= htmlspecialchars($prod['Nom']) ?></td>
                                    <td><?= htmlspecialchars(substr($prod['Description'], 0, 100)) ?><?= strlen($prod['Description']) > 100 ? '...' : '' ?></td>
                                    <td><?= htmlspecialchars(number_format($prod['Prix'], 2, ',', ' ')) ?></td>
                                    <td><?= htmlspecialchars($prod['Stock']) ?></td>
                                    <td>
                                        <form method="post" action="" class="form-inline">
                                            <input type="hidden" name="action" value="add_to_panier">
                                            <input type="hidden" name="product_id" value="<?= htmlspecialchars($prod['idProduit']) ?>">
                                            <input type="number" name="quantite" value="1" min="1" max="<?= htmlspecialchars($prod['Stock']) ?>" class="form-control mr-2" required>
                                            <button type="submit" class="btn btn-sm btn-primary">Ajouter au Projet</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Intégration de DataTables CSS et JS via CDN -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.5.1.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<!-- Intégration de DataTables pour les Tableaux "Panier" et "Ajouter des Produits" -->
<script>
    $(document).ready(function() {
        // Initialisation de DataTables pour le panier
        $('#panierTable').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/French.json"
            },
            "paging": false,
            "searching": false,
            "info": false,
            "ordering": false
        });

        // Initialisation de DataTables pour la liste des produits avec recherche, tri et pagination
        $('#allProductsTable').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/French.json"
            },
            "pageLength": 10,
            "lengthMenu": [5, 10, 25, 50, 100],
            "ordering": true,
            "searching": true
        });
    });
</script>

<?php include '../footer.php'; ?>