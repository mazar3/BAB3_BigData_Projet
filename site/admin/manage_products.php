<?php
// admin/manage_products.php
global $connection;
include '../header.php';
include '../db_connect.php';

// Vérifier si l'utilisateur est connecté et a le rôle "Administrateur"
if (!isset($_SESSION['user_id']) || get_role() !== 'Administrateur') {
    header("Location: ../login.php");
    exit();
}

// Traitement de la suppression d'un produit
$delete_message = '';
$delete_success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_product') {
    $delete_product_id = intval($_POST['product_id']);

    // Supprimer le produit
    $stmt = $connection->prepare("DELETE FROM Produit WHERE idProduit = ?");
    $stmt->bind_param("i", $delete_product_id);
    if ($stmt->execute()) {
        $delete_message = "Produit supprimé avec succès.";
        $delete_success = true;
    } else {
        $delete_message = "Erreur lors de la suppression du produit : " . htmlspecialchars($stmt->error);
    }
    $stmt->close();
}

// Initialiser les variables pour le formulaire d'ajout
$add_message = '';
$add_success = false;

// Traitement du formulaire d'ajout de produit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_product') {
    $nom = trim($_POST['nom']);
    $description = trim($_POST['description']);
    $prix = floatval($_POST['prix']);
    $stock = intval($_POST['stock']);
    $type_produit_id = intval($_POST['type_produit_id']);
    $fournisseur_id = intval($_POST['fournisseur_id']);

    // Validation des champs
    if (empty($nom) || empty($description) || $prix <= 0 || $stock < 0 || empty($type_produit_id) || empty($fournisseur_id)) {
        $add_message = "Veuillez remplir tous les champs avec des valeurs valides.";
    } else {
        // Insérer le nouveau produit
        $stmt = $connection->prepare("INSERT INTO Produit (Nom, Description, Prix, Stock, idTypeProduit, idFournisseur) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdiii", $nom, $description, $prix, $stock, $type_produit_id, $fournisseur_id);
        if ($stmt->execute()) {
            $add_message = "Produit ajouté avec succès.";
            $add_success = true;
        } else {
            $add_message = "Erreur lors de l'ajout du produit : " . htmlspecialchars($stmt->error);
        }
        $stmt->close();
    }
}

// Récupérer la liste des produits avec les informations du type de produit et du fournisseur
$stmt = $connection->prepare("
    SELECT 
        p.idProduit, 
        p.Nom AS ProduitNom, 
        p.Description, 
        p.Prix, 
        p.Stock, 
        tp.Nom AS TypeProduit, 
        f.Nom AS Fournisseur
    FROM Produit p
    LEFT JOIN Type_produit tp ON p.idTypeProduit = tp.idTypeProduit
    LEFT JOIN Fournisseur f ON p.idFournisseur = f.idFournisseur
    ORDER BY p.idProduit DESC
");
$stmt->execute();
$result = $stmt->get_result();
$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}
$stmt->close();

// Récupérer la liste des types de produits pour le formulaire d'ajout
$stmt = $connection->prepare("SELECT idTypeProduit, Nom FROM Type_produit ORDER BY Nom ASC");
$stmt->execute();
$result = $stmt->get_result();
$type_produits = [];
while ($row = $result->fetch_assoc()) {
    $type_produits[] = $row;
}
$stmt->close();

// Récupérer la liste des fournisseurs pour le formulaire d'ajout
$stmt = $connection->prepare("SELECT idFournisseur, Nom FROM Fournisseur ORDER BY Nom ASC");
$stmt->execute();
$result = $stmt->get_result();
$fournisseurs = [];
while ($row = $result->fetch_assoc()) {
    $fournisseurs[] = $row;
}
$stmt->close();

$connection->close();
?>

<div class="container mt-5">
    <h1 class="mb-4">Gestion des produits</h1>

    <!-- Messages de confirmation -->
    <?php if ($delete_message): ?>
        <div class="alert <?= $delete_success ? 'alert-success' : 'alert-danger' ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($delete_message) ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Fermer">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <!-- Formulaire d'ajout de produit -->
    <div class="card mb-4">
        <div class="card-header">
            <h3>Ajouter un nouveau produit</h3>
        </div>
        <div class="card-body">
            <?php if ($add_message): ?>
                <div class="alert <?= $add_success ? 'alert-success' : 'alert-danger' ?> alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($add_message) ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Fermer">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>
            <form method="post" action="">
                <input type="hidden" name="action" value="add_product">
                <div class="form-group">
                    <label for="nom">Nom du produit</label>
                    <input type="text" class="form-control" id="nom" name="nom" required>
                </div>
                <div class="form-group">
                    <label for="description">Description du produit</label>
                    <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label for="prix">Prix (€)</label>
                        <input type="number" step="0.01" class="form-control" id="prix" name="prix" required>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="stock">Stock</label>
                        <input type="number" class="form-control" id="stock" name="stock" required>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="type_produit_id">Type de produit</label>
                        <select class="form-control" id="type_produit_id" name="type_produit_id" required>
                            <option value="">Sélectionnez un type</option>
                            <?php foreach ($type_produits as $type): ?>
                                <option value="<?= htmlspecialchars($type['idTypeProduit']) ?>"><?= htmlspecialchars($type['Nom']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="fournisseur_id">Fournisseur</label>
                        <select class="form-control" id="fournisseur_id" name="fournisseur_id" required>
                            <option value="">Sélectionnez un fournisseur</option>
                            <?php foreach ($fournisseurs as $fournisseur): ?>
                                <option value="<?= htmlspecialchars($fournisseur['idFournisseur']) ?>"><?= htmlspecialchars($fournisseur['Nom']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Ajouter le produit</button>
            </form>
        </div>
    </div>

    <!-- Table des produits -->
    <div class="card">
        <div class="card-header">
            <h3>Liste des Produits</h3>
        </div>
        <div class="card-body">
            <?php if (count($products) === 0): ?>
                <p>Aucun produit trouvé.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead class="thead-dark">
                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Description</th>
                            <th>Prix (€)</th>
                            <th>Stock</th>
                            <th>Type de Produit</th>
                            <th>Fournisseur</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td><?= htmlspecialchars($product['idProduit']) ?></td>
                                <td><?= htmlspecialchars($product['ProduitNom']) ?></td>
                                <td><?= htmlspecialchars(substr($product['Description'], 0, 100)) ?><?= strlen($product['Description']) > 100 ? '...' : '' ?></td>
                                <td><?= htmlspecialchars(number_format($product['Prix'], 2, ',', ' ')) ?></td>
                                <td><?= htmlspecialchars($product['Stock']) ?></td>
                                <td><?= htmlspecialchars($product['TypeProduit'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($product['Fournisseur'] ?? 'N/A') ?></td>
                                <td>
                                    <!-- Bouton d'édition -->
                                    <a href="edit_product.php?id=<?= htmlspecialchars($product['idProduit']) ?>" class="btn btn-sm btn-warning mb-1">Éditer</a>

                                    <!-- Bouton de suppression -->
                                    <form method="post" action="" style="display:inline-block;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce produit ? Cette action supprimera également les associations avec les commandes.')">
                                        <input type="hidden" name="action" value="delete_product">
                                        <input type="hidden" name="product_id" value="<?= htmlspecialchars($product['idProduit']) ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">Supprimer</button>
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

<?php include '../footer.php'; ?>
