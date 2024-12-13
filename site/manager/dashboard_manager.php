<?php
// admin/dashboard_manager.php
global $connection;
include '../header.php';
include '../db_connect.php';

// Vérifier si l'utilisateur est connecté et a le rôle "Responsable de projet"
if (!isset($_SESSION['user_id']) || get_role() !== 'Responsable de projet') {
    header("Location: ../login.php");
    exit();
}

$manager_id = $_SESSION['user_id'];

// Récupérer la liste des projets assignés au manager
$stmt = $connection->prepare("
    SELECT 
        p.idProjet, 
        p.Nom AS ProjetNom, 
        p.Description, 
        p.Statut, 
        DATE_FORMAT(p.Date_Debut, '%d/%m/%Y') AS DateDebutFormat
    FROM Projet p
    JOIN Projet_manager pm ON p.idProjet = pm.idProjet
    WHERE pm.idUtilisateur = ?
    ORDER BY p.idProjet DESC
");
$stmt->bind_param("i", $manager_id);
$stmt->execute();
$result = $stmt->get_result();
$projects = [];
while ($row = $result->fetch_assoc()) {
    $projects[] = $row;
}
$stmt->close();

// Récupérer les alertes sur les stocks faibles (stock < 10)
$stmt = $connection->prepare("
    SELECT COUNT(*) 
    FROM Produit 
    WHERE Stock < 10
");
$stmt->execute();
$stmt->bind_result($low_stock_count);
$stmt->fetch();
$stmt->close();

$connection->close();
?>

<div class="container mt-5">
    <h1 class="mb-4">Tableau de Bord Manager</h1>

    <!-- Alertes sur les stocks faibles -->
    <?php if ($low_stock_count > 0): ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            Il y a <?= htmlspecialchars($low_stock_count) ?> produit(s) en rupture de stock.
            <button type="button" class="close" data-dismiss="alert" aria-label="Fermer">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <!-- Liste des projets du manager -->
    <div class="card">
        <div class="card-header">
            <h3>Mes Projets</h3>
        </div>
        <div class="card-body">
            <?php if (count($projects) === 0): ?>
                <p>Aucun projet assigné.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table id="projectsTable" class="table table-striped table-bordered">
                        <thead class="thead-dark">
                        <tr>
                            <th>ID</th>
                            <th>Nom du Projet</th>
                            <th>Description</th>
                            <th>Statut</th>
                            <th>Date de Début</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($projects as $project): ?>
                            <tr>
                                <td><?= htmlspecialchars($project['idProjet']) ?></td>
                                <td><?= htmlspecialchars($project['ProjetNom']) ?></td>
                                <td><?= htmlspecialchars(substr($project['Description'], 0, 100)) ?><?= strlen($project['Description']) > 100 ? '...' : '' ?></td>
                                <td><?= htmlspecialchars($project['Statut']) ?></td>
                                <td><?= htmlspecialchars($project['DateDebutFormat']) ?></td>
                                <td>
                                    <a href="project.php?idProjet=<?= htmlspecialchars($project['idProjet']) ?>" class="btn btn-sm btn-primary">Accéder au Projet</a>
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

<script>
    $(document).ready(function() {
        $('#projectsTable').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/French.json"
            },
            "pageLength": 10,
            "lengthMenu": [5, 10, 25, 50, 100]
        });
    });
</script>

<?php include '../footer.php'; ?>
