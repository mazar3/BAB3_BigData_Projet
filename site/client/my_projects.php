<?php
// my_projects.php
global $connection;
include '../header.php';
include '../db_connect.php';

// Vérifier si l'utilisateur est connecté et a le rôle "Client"
if (!isset($_SESSION['user_id']) || get_role() !== 'Client') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Récupérer la liste des projets de l'utilisateur
$stmt = $connection->prepare("
    SELECT idProjet, Nom, Description, Statut, DATE_FORMAT(Date_Debut, '%d/%m/%Y') as DateDebutFormat
    FROM Projet
    WHERE idUtilisateur = ?
    ORDER BY idProjet DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$projects = [];
while ($row = $result->fetch_assoc()) {
    $projects[] = $row;
}
$stmt->close();
$connection->close();
?>

<div class="container mt-5">
    <h2>Mes Projets</h2>
    <p class="text-muted mb-4">Retrouvez ici la liste de tous vos projets, avec leur statut actuel.</p>

    <?php if (count($projects) === 0): ?>
        <div class="alert alert-info" role="alert">
            Vous n'avez pas encore créé de projet. <a href="create_project.php">Créer un projet</a>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead class="thead-dark">
                <tr>
                    <th>Nom</th>
                    <th>Description</th>
                    <th>Date de début</th>
                    <th>Statut</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($projects as $project): ?>
                    <tr>
                        <td><?= htmlspecialchars($project['Nom']) ?></td>
                        <td><?= htmlspecialchars(substr($project['Description'], 0, 100)) ?>...</td>
                        <td><?= htmlspecialchars($project['DateDebutFormat']) ?></td>
                        <td><?= htmlspecialchars($project['Statut']) ?></td>
                        <td>
                            <a href="project.php?idProjet=<?= htmlspecialchars($project['idProjet']) ?>" class="btn btn-sm btn-info">Accéder au projet</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <div class="mt-4">
        <a href="create_project.php" class="btn btn-primary">Créer un nouveau projet</a>
        <a href="dashboard_client.php" class="btn btn-secondary">Retour au tableau de bord</a>
    </div>
</div>

<?php include '../footer.php'; ?>
