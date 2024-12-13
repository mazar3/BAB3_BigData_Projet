<?php
// client/project.php
global $connection;
include '../header.php';
include '../db_connect.php';

// Vérifier si l'utilisateur est connecté et a le rôle "Client"
if (!isset($_SESSION['user_id']) || get_role() !== 'Client') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Vérifier si idProjet est passé dans l'URL
if (!isset($_GET['idProjet']) || empty($_GET['idProjet'])) {
    header("Location: my_projects.php");
    exit();
}

$idProjet = intval($_GET['idProjet']);

// Récupérer les détails du projet sans la date de fin
$stmt = $connection->prepare("
    SELECT p.Nom, p.Description, p.Statut, DATE_FORMAT(p.Date_Debut, '%d/%m/%Y') AS DateDebutFormat, p.idPanier
    FROM Projet p
    WHERE p.idProjet = ? AND p.idUtilisateur = ?
");
$stmt->bind_param("ii", $idProjet, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Projet non trouvé ou n'appartient pas à l'utilisateur
    echo '<div class="container mt-5"><div class="alert alert-danger">Projet non trouvé ou vous n\'avez pas l\'autorisation d\'y accéder.</div></div>';
    include '../footer.php';
    exit();
}

$project = $result->fetch_assoc();
$stmt->close();

$panier_id = $project['idPanier'];

// Récupérer les responsables (managers) du projet
$stmt = $connection->prepare("
    SELECT u.Nom, u.Prenom
    FROM Projet_manager pm
    JOIN Utilisateur u ON pm.idUtilisateur = u.idUtilisateur
    JOIN Role r ON u.idRole = r.idRole
    WHERE pm.idProjet = ? AND r.Description = 'Responsable de projet'
");
$stmt->bind_param("i", $idProjet);
$stmt->execute();
$result = $stmt->get_result();

$managers = [];
while ($row = $result->fetch_assoc()) {
    $managers[] = $row;
}
$stmt->close();

// Gestion des actions du client (accepter ou refuser le devis, ajout de commentaire)
$message = '';
$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    if ($action === 'add_comment') {
        // Ajout de commentaire
        $comment = trim($_POST['comment']);
        if (!empty($comment)) {
            $stmt = $connection->prepare("
                INSERT INTO Commentaires (idProjet, idUtilisateur, Commentaire, Date_Commentaire)
                VALUES (?, ?, ?, NOW())
            ");
            if ($stmt) {
                $stmt->bind_param("iis", $idProjet, $user_id, $comment);
                if ($stmt->execute()) {
                    $message = "Votre commentaire a été ajouté avec succès.";
                    $success = true;
                } else {
                    $message = "Erreur lors de l'ajout du commentaire : " . htmlspecialchars($stmt->error);
                }
                $stmt->close();
            } else {
                $message = "Erreur de préparation de la requête : " . htmlspecialchars($connection->error);
            }
        } else {
            $message = "Le champ commentaire ne peut pas être vide.";
        }
    } elseif ($action === 'accept_devis' || $action === 'refuse_devis') {
        // Accepter ou refuser le devis
        if ($project['Statut'] === 'Devis Envoyé') {
            $nouveau_statut = ($action === 'accept_devis') ? 'Terminé' : 'Refusé';

            $stmt = $connection->prepare("UPDATE Projet SET Statut = ? WHERE idProjet = ?");
            $stmt->bind_param("si", $nouveau_statut, $idProjet);
            if ($stmt->execute()) {
                $message = $action === 'accept_devis'
                    ? "Vous avez accepté le devis. Le projet est désormais '" . $nouveau_statut . "'."
                    : "Vous avez refusé le devis. Le projet est désormais '" . $nouveau_statut . "'.";
                $success = true;
                $project['Statut'] = $nouveau_statut;
            } else {
                $message = "Erreur lors de la mise à jour du statut du projet : " . htmlspecialchars($stmt->error);
            }
            $stmt->close();
        } else {
            $message = "Le devis n'est pas dans l'état 'Devis Envoyé', vous ne pouvez pas effectuer cette action.";
        }
    }
}

// Récupérer les commentaires du projet
$comments = [];
$stmt = $connection->prepare("
    SELECT c.Commentaire, c.Date_Commentaire, u.Nom, u.Prenom
    FROM Commentaires c
    JOIN Utilisateur u ON c.idUtilisateur = u.idUtilisateur
    WHERE c.idProjet = ?
    ORDER BY c.Date_Commentaire DESC
");
$stmt->bind_param("i", $idProjet);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $comments[] = $row;
}
$stmt->close();

// Si le devis est envoyé, terminé ou refusé, on récupère le contenu du panier
$panier = [];
$total_montant = 0;
if (in_array($project['Statut'], ['Devis Envoyé', 'Terminé', 'Refusé'])) {
    $stmt = $connection->prepare("
        SELECT p.Nom, p.Prix, pp.Quantite
        FROM Panier_produit pp
        JOIN Produit p ON pp.idProduit = p.idProduit
        WHERE pp.idPanier = ?
    ");
    $stmt->bind_param("i", $panier_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $panier[] = $row;
        $total_montant += $row['Prix'] * $row['Quantite'];
    }
    $stmt->close();
}

$connection->close();
?>

<div class="container mt-5">
    <?php if ($message): ?>
        <div class="alert <?= $success ? 'alert-success' : 'alert-danger' ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($message) ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Fermer">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-header">
            <h3><?= htmlspecialchars($project['Nom']) ?></h3>
        </div>
        <div class="card-body">
            <p><strong>Description :</strong> <?= nl2br(htmlspecialchars($project['Description'])) ?></p>
            <p><strong>Date de début :</strong> <?= htmlspecialchars($project['DateDebutFormat']) ?></p>
            <p><strong>Statut :</strong> <?= htmlspecialchars($project['Statut']) ?></p>
            <?php if (count($managers) > 0): ?>
                <p><strong>Responsable(s) :</strong>
                    <?php foreach ($managers as $manager): ?>
                        <?= htmlspecialchars($manager['Prenom'] . ' ' . $manager['Nom']) ?><?= end($managers) === $manager ? '' : ', ' ?>
                    <?php endforeach; ?>
                </p>
            <?php else: ?>
                <p><strong>Responsable(s) :</strong> Aucun responsable assigné.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Section Commentaires -->
    <div class="card">
        <div class="card-header">
            <h4>Commentaires</h4>
        </div>
        <div class="card-body">
            <!-- Formulaire d'ajout de commentaire -->
            <form method="post" action="">
                <input type="hidden" name="action" value="add_comment">
                <div class="form-group">
                    <label for="comment">Ajouter un commentaire</label>
                    <textarea class="form-control" id="comment" name="comment" rows="3" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Ajouter</button>
            </form>

            <hr>

            <!-- Liste des commentaires -->
            <?php if (count($comments) > 0): ?>
                <?php foreach ($comments as $cmt): ?>
                    <div class="media mb-3">
                        <div class="media-body">
                            <h5 class="mt-0"><?= htmlspecialchars($cmt['Prenom'] . ' ' . $cmt['Nom']) ?></h5>
                            <p><?= nl2br(htmlspecialchars($cmt['Commentaire'])) ?></p>
                            <small class="text-muted"><?= htmlspecialchars($cmt['Date_Commentaire']) ?></small>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Aucun commentaire pour le moment.</p>
            <?php endif; ?>
        </div>
    </div>

    <?php if (in_array($project['Statut'], ['Devis Envoyé', 'Terminé', 'Refusé'])): ?>
        <!-- Panier (Devis) -->
        <div class="card mt-4">
            <div class="card-header">
                <h3>Devis</h3>
            </div>
            <div class="card-body">
                <?php if (count($panier) === 0): ?>
                    <p>Aucun produit dans le devis.</p>
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

                    <?php if ($project['Statut'] === 'Devis Envoyé'): ?>
                        <form method="post" action="" class="mt-3">
                            <input type="hidden" name="action" value="accept_devis">
                            <button type="submit" class="btn btn-success mr-2">Accepter le Devis</button>
                        </form>
                        <form method="post" action="" class="mt-3">
                            <input type="hidden" name="action" value="refuse_devis">
                            <button type="submit" class="btn btn-danger">Refuser le Devis</button>
                        </form>
                    <?php elseif ($project['Statut'] === 'Terminé'): ?>
                        <p class="mt-3">Vous avez accepté le devis. Le projet est maintenant terminé.</p>
                        <p>Voici votre facture (PDF) :</p>
                        <!-- Faux bouton de téléchargement de la facture -->
                        <button class="btn btn-info">Télécharger la Facture (PDF)</button>
                        <p class="mt-3">Veuillez effectuer le paiement par virement bancaire sur le compte suivant :</p>
                        <ul>
                            <li><strong>IBAN :</strong> FR76 1234 5678 9012 3456 7890 189</li>
                            <li><strong>BIC :</strong> ABCD1234XXX</li>
                        </ul>
                        <p>Merci de votre confiance.</p>
                    <?php elseif ($project['Statut'] === 'Refusé'): ?>
                        <p class="mt-3">Vous avez refusé le devis. Le projet est marqué comme refusé.</p>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Intégration de DataTables CSS et JS via CDN -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.5.1.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
    $(document).ready(function() {
        <?php if (in_array($project['Statut'], ['Devis Envoyé', 'Terminé', 'Refusé']) && count($panier) > 0): ?>
        $('#panierTable').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/French.json"
            },
            "pageLength": 10,
            "lengthMenu": [5, 10, 25, 50, 100],
            "ordering": true,
            "searching": true,
            "info": true,
            "paging": true
        });
        <?php endif; ?>
    });
</script>

<?php include '../footer.php'; ?>