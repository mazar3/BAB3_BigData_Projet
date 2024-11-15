<?php
global $connection;
session_start();

// Vérifier si l'utilisateur est connecté et est un Administrateur
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role_description']) || $_SESSION['role_description'] !== 'Administrateur') {
    header("Location: login.php");
    exit();
}

include 'db_connect.php';

$message = '';

// Traitement de la mise à jour des rôles
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_role'])) {
    $user_id = intval($_POST['user_id']);
    $new_role_id = intval($_POST['idRole']);

    // Récupérer le rôle actuel de l'utilisateur cible
    $stmt = $connection->prepare("SELECT r.description FROM utilisateur u JOIN Role r ON u.idRole = r.idRole WHERE u.idUtilisateur = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($current_role);
    $stmt->fetch();
    $stmt->close();

    // Si l'utilisateur cible est un Administrateur, empêcher la modification
    if ($current_role === 'Administrateur') {
        $message = "<div class='alert alert-danger'>Erreur : Vous ne pouvez pas modifier le rôle d'un autre Administrateur.</div>";
    } else {
        // Mettre à jour le rôle de l'utilisateur
        $update_stmt = $connection->prepare("UPDATE utilisateur SET idRole = ? WHERE idUtilisateur = ?");
        $update_stmt->bind_param("ii", $new_role_id, $user_id);
        if ($update_stmt->execute()) {
            $message = "<div class='alert alert-success'>Le rôle de l'utilisateur a été mis à jour avec succès.</div>";
        } else {
            $message = "<div class='alert alert-danger'>Erreur lors de la mise à jour du rôle : " . htmlspecialchars($update_stmt->error) . "</div>";
        }
        $update_stmt->close();
    }
}

// Récupérer la liste des utilisateurs avec leurs rôles
$users = [];
$user_stmt = $connection->prepare("SELECT u.idUtilisateur, u.Email, u.Nom, u.Prenom, r.idRole, r.description FROM utilisateur u JOIN Role r ON u.idRole = r.idRole ORDER BY u.Nom ASC, u.Prenom ASC");
$user_stmt->execute();
$result = $user_stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}
$user_stmt->close();

// Récupérer la liste des rôles disponibles
$roles = [];
$role_stmt = $connection->prepare("SELECT idRole, description FROM Role");
$role_stmt->execute();
$role_result = $role_stmt->get_result();
while ($role = $role_result->fetch_assoc()) {
    $roles[] = $role;
}
$role_stmt->close();

// Fermer la connexion
$connection->close();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Gérer les Utilisateurs</title>
    <!-- Inclusion de Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
    <!-- Optionnel : Inclure un favicon ou d'autres métadonnées -->
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
                <a class="nav-link" href="admin_dashboard.php">Accueil</a>
            </li>
            <li class="nav-item active">
                <a class="nav-link" href="manage_users.php">Gérer les Utilisateurs <span class="sr-only">(actuel)</span></a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="manage_projects.php">Gérer les Projets</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="view_financials.php">Voir les Informations Financières</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="logout.php">Se Déconnecter</a>
            </li>
        </ul>
    </div>
</nav>

<div class="container mt-5">
    <h2>Gestion des Utilisateurs</h2>
    <?php if ($message): ?>
        <?= $message ?>
    <?php endif; ?>
    <table class="table table-striped table-bordered mt-3">
        <thead class="thead-dark">
        <tr>
            <th>Email</th>
            <th>Nom</th>
            <th>Prénom</th>
            <th>Rôle</th>
            <th>Action</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?= htmlspecialchars($user['Email']) ?></td>
                <td><?= htmlspecialchars($user['Nom']) ?></td>
                <td><?= htmlspecialchars($user['Prenom']) ?></td>
                <td>
                    <?php if ($user['description'] === 'Administrateur'): ?>
                        <?= htmlspecialchars($user['description']) ?>
                    <?php else: ?>
                    <form method="post" action="manage_users.php">
                        <input type="hidden" name="user_id" value="<?= $user['idUtilisateur'] ?>">
                        <div class="form-group">
                            <select name="idRole" class="form-control">
                                <?php foreach ($roles as $role): ?>
                                    <option value="<?= $role['idRole'] ?>" <?= $user['idRole'] == $role['idRole'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($role['description']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                </td>
                <td>
                    <button type="submit" name="update_role" class="btn btn-primary btn-sm">Mettre à jour</button>
                    </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Inclusion de Bootstrap JS et ses dépendances -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
