<?php
// admin/edit_product.php
global $connection;
include '../header.php';
include '../db_connect.php';

// Vérifier si l'utilisateur est connecté et a le rôle "Administrateur"
if (!isset($_SESSION['user_id']) || get_role() !== 'Administrateur') {
    header("Location: ../login.php");
    exit();
}

// Vérifier si l'ID du produit à éditer est fourni
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: manage_products.php");
    exit();
}

$product_id = intval($_GET['id']);

// Initialiser les variables
$edit_message = '';
$edit_success = false;

// Récupérer les détails du produit
$stmt = $connection->prepare("
    SELECT p.Nom, p.Description, p.Prix, p.Stock, p.idTypeProduit, p.idFournisseur, tp.Nom AS TypeProduitNom, f.Nom AS FournisseurNom
    FROM Produit p
    LEFT JOIN Type_produit tp ON p.idTypeProduit = tp.idTypeProduit
    LEFT JOIN Fournisseur f ON p.idFournisseur = f.idFournisseur
    WHERE p.idProduit = ?
");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$stmt->bind_result($nom, $description, $prix, $stock, $type_produit_id, $fournisseur_id, $type_produit_nom, $fournisseur_nom);
if (!$stmt->fetch()) {
    // Produit non trouvé
    $stmt->close();
    echo '<div class="container mt-5"><div class="alert alert-danger">Produit non trouvé.</div></div>';
    include '../footer.php';
    exit();
}
$stmt->close();

// Récupérer la liste des types de produits pour le formulaire d'édition
$stmt = $connection->prepare("SELECT idTypeProduit, Nom FROM Type_produit ORDER BY Nom ASC");
$stmt->execute();
$result = $stmt->get_result();
$type_produits = [];
while ($row = $result->fetch_assoc()) {
    $type_produits[] = $row;
}
$stmt->close();

// Récupérer la liste des fournisseurs pour le formulaire d'édition
$stmt = $connection->prepare("SELECT idFournisseur, Nom FROM Fournisseur ORDER BY Nom ASC");
$stmt->execute();
$result = $stmt->get_result();
$fournisseurs = [];
while ($row = $result->fetch_assoc()) {
    $fournisseurs[] = $row;
}
$stmt->close();

// Traitement du formulaire d'édition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_product') {
    $new_nom = trim($_POST['nom']);
    $new_description = trim($_POST['description']);
    $new_prix = floatval($_POST['prix']);
    $new_stock = intval($_POST['stock']);
    $new_type_produit_id = intval($_POST['type_produit_id']);
    $new_fournisseur_id = intval($_POST['fournisseur_id']);

    // Validation des champs
    if (empty($new_nom) || empty($new_description) || $new_prix <= 0 || $new_stock < 0 || empty($new_type_produit_id) || empty($new_fournisseur_id)) {
        $edit_message = "Veuillez remplir tous les champs avec des valeurs valides.";
    } else {
        // Mettre à jour le produit
        $stmt = $connection->prepare("
            UPDATE Produit 
            SET Nom = ?, Description = ?, Prix = ?, Stock = ?, idTypeProduit = ?, idFournisseur = ?
            WHERE idProduit = ?
        ");
        $stmt->bind_param("ssdiiii", $new_nom, $new_description, $new_prix, $new_stock, $new_type_produit_id, $new_fournisseur_id, $product_id);
        if ($stmt->execute()) {
            $edit_message = "Produit mis à jour avec succès.";
            $edit_success = true;

            // Mettre à jour les variables avec les nouvelles valeurs pour l'affichage
            $nom = $new_nom;
            $description = $new_description;
            $prix = $new_prix;
            $stock = $new_stock;
            $type_produit_id = $new_type_produit_id;
            $fournisseur_id = $new_fournisseur_id;
        } else {
            $edit_message = "Erreur lors de la mise à jour du produit : " . htmlspecialchars($stmt->error);
        }
        $stmt->close();
    }
}

$connection->close();
?>

<div class="container mt-5">
    <h1 class="mb-4">Éditer le produit</h1>

    <div class="card">
        <div class="card-header">
            <h3>Modifier les détails du produit</h3>
        </div>
        <div class="card-body">
            <?php if ($edit_message): ?>
                <div class="alert <?= $edit_success ? 'alert-success' : 'alert-danger' ?> alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($edit_message) ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Fermer">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>
            <form method="post" action="">
                <input type="hidden" name="action" value="edit_product">
                <div class="form-group">
                    <label for="nom">Nom du produit</label>
                    <input type="text" class="form-control" id="nom" name="nom" value="<?= htmlspecialchars($nom) ?>" required>
                </div>
                <div class="form-group">
                    <label for="description">Description du produit</label>
                    <textarea class="form-control" id="description" name="description" rows="3" required><?= htmlspecialchars($description) ?></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label for="prix">Prix (€)</label>
                        <input type="number" step="0.01" class="form-control" id="prix" name="prix" value="<?= htmlspecialchars($prix) ?>" required>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="stock">Stock</label>
                        <input type="number" class="form-control" id="stock" name="stock" value="<?= htmlspecialchars($stock) ?>" required>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="type_produit_id">Type de produit</label>
                        <select class="form-control" id="type_produit_id" name="type_produit_id" required>
                            <option value="">Sélectionnez un type</option>
                            <?php foreach ($type_produits as $type): ?>
                                <option value="<?= htmlspecialchars($type['idTypeProduit']) ?>" <?= ($type['idTypeProduit'] == $type_produit_id) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($type['Nom']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="fournisseur_id">Fournisseur</label>
                        <select class="form-control" id="fournisseur_id" name="fournisseur_id" required>
                            <option value="">Sélectionnez un fournisseur</option>
                            <?php foreach ($fournisseurs as $fournisseur): ?>
                                <option value="<?= htmlspecialchars($fournisseur['idFournisseur']) ?>" <?= ($fournisseur['idFournisseur'] == $fournisseur_id) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($fournisseur['Nom']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Mettre à jour le produit</button>
                <a href="manage_products.php" class="btn btn-secondary">Retour à la gestion des produits</a>
            </form>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?>
