<?php
// admin/manage_projects.php
global $connection;
include '../header.php';
include '../db_connect.php';

// Vérifier si l'utilisateur est connecté et a le rôle "Administrateur"
if (!isset($_SESSION['user_id']) || get_role() !== 'Administrateur') {
    header("Location: ../login.php");
    exit();
}

// Traitement de la suppression d'un projet
$delete_message = '';
$delete_success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_project') {
    $delete_project_id = intval($_POST['project_id']);

    // Supprimer les associations dans Projet_manager
    $stmt = $connection->prepare("DELETE FROM Projet_manager WHERE idProjet = ?");
    $stmt->bind_param("i", $delete_project_id);
    if ($stmt->execute()) {
        $stmt->close();

        // Supprimer le projet
        $stmt = $connection->prepare("DELETE FROM Projet WHERE idProjet = ?");
        $stmt->bind_param("i", $delete_project_id);
        if ($stmt->execute()) {
            $delete_message = "Projet supprimé avec succès.";
            $delete_success = true;
        } else {
            $delete_message = "Erreur lors de la suppression du projet : " . htmlspecialchars($stmt->error);
        }
    } else {
        $delete_message = "Erreur lors de la suppression des associations du projet : " . htmlspecialchars($stmt->error);
    }
    $stmt->close();
}

// Récupérer la liste des projets avec les informations du client et des responsables
$stmt = $connection->prepare("
    SELECT 
        p.idProjet, 
        p.Nom AS ProjetNom, 
        p.Description, 
        p.Statut, 
        DATE_FORMAT(p.Date_Debut, '%d/%m/%Y') AS DateDebutFormat, 
        u.Nom AS ClientNom, 
        u.Prenom AS ClientPrenom
    FROM Projet p
    JOIN Utilisateur u ON p.idUtilisateur = u.idUtilisateur
    ORDER BY p.idProjet DESC
");
$stmt->execute();
$result = $stmt->get_result();
$projects = [];
while ($row = $result->fetch_assoc()) {
    // Récupérer les responsables pour chaque projet
    $project_id = $row['idProjet'];
    $stmt_managers = $connection->prepare("
        SELECT u.Nom, u.Prenom
        FROM Projet_manager pm
        JOIN Utilisateur u ON pm.idUtilisateur = u.idUtilisateur
        JOIN Role r ON u.idRole = r.idRole
        WHERE pm.idProjet = ? AND r.Description = 'Responsable de projet'
    ");
    $stmt_managers->bind_param("i", $project_id);
    $stmt_managers->execute();
    $result_managers = $stmt_managers->get_result();
    $managers = [];
    while ($manager = $result_managers->fetch_assoc()) {
        $managers[] = $manager['Prenom'] . ' ' . $manager['Nom'];
    }
    $row['Managers'] = $managers;
    $projects[] = $row;
    $stmt_managers->close();
}
$stmt->close();

// Récupérer la liste des responsables (managers) pour les assignations éventuelles
$stmt = $connection->prepare("
    SELECT u.idUtilisateur, u.Prenom, u.Nom
    FROM Utilisateur u
    JOIN Role r ON u.idRole = r.idRole
    WHERE r.Description = 'Responsable de projet'
    ORDER BY u.Prenom ASC, u.Nom ASC
");
$stmt->execute();
$result = $stmt->get_result();
$available_managers = [];
while ($row = $result->fetch_assoc()) {
    $available_managers[] = $row;
}
$stmt->close();

$connection->close();
?>

<div class="container mt-5">
    <h1 class="mb-4">Gestion des Projets</h1>

    <!-- Messages de confirmation -->
    <?php if ($delete_message): ?>
        <div class="alert <?= $delete_success ? 'alert-success' : 'alert-danger' ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($delete_message) ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Fermer">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <!-- Table des projets -->
    <div class="card">
        <div class="card-header">
            <h3>Liste des Projets</h3>
        </div>
        <div class="card-body">
            <?php if (count($projects) === 0): ?>
                <p>Aucun projet trouvé.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead class="thead-dark">
                        <tr>
                            <th>ID</th>
                            <th>Nom du Projet</th>
                            <th>Description</th>
                            <th>Statut</th>
                            <th>Date de Début</th>
                            <th>Client</th>
                            <th>Responsables</th>
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
                                <td><?= htmlspecialchars($project['ClientPrenom'] . ' ' . $project['ClientNom']) ?></td>
                                <td>
                                    <?php
                                    if (count($project['Managers']) > 0) {
                                        echo htmlspecialchars(implode(', ', $project['Managers']));
                                    } else {
                                        echo 'Aucun responsable assigné.';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <!-- Bouton d'édition -->
                                    <a href="edit_project.php?id=<?= htmlspecialchars($project['idProjet']) ?>" class="btn btn-sm btn-warning mb-1">Éditer</a>

                                    <!-- Bouton de suppression -->
                                    <form method="post" action="" style="display:inline-block;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce projet ?');">
                                        <input type="hidden" name="action" value="delete_project">
                                        <input type="hidden" name="project_id" value="<?= htmlspecialchars($project['idProjet']) ?>">
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
