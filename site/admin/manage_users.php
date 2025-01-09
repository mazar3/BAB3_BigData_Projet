<?php
// admin/manage_users.php
global $connection;
include '../header.php';
include '../db_connect.php';

// Vérifier si l'utilisateur est connecté et a le rôle "Administrateur"
if (!isset($_SESSION['user_id']) || get_role() !== 'Administrateur') {
    header("Location: ../login.php");
    exit();
}

// Initialiser les variables pour le formulaire d'ajout
$add_message = '';
$add_success = false;

// Traitement du formulaire d'ajout d'utilisateur
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_user') {
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $role_id = intval($_POST['role_id']);

    // Validation des champs
    if (empty($nom) || empty($prenom) || empty($email) || empty($password) || empty($role_id)) {
        $add_message = "Veuillez remplir tous les champs.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $add_message = "L'adresse email n'est pas valide.";
    } else {
        // Vérifier si l'email existe déjà
        $stmt = $connection->prepare("SELECT idUtilisateur FROM Utilisateur WHERE Email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $add_message = "Cet email est déjà utilisé.";
        } else {
            // Insérer le nouvel utilisateur
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $stmt_insert = $connection->prepare("INSERT INTO Utilisateur (Nom, Prenom, Email, Password, idRole) VALUES (?, ?, ?, ?, ?)");
            $stmt_insert->bind_param("ssssi", $nom, $prenom, $email, $hashed_password, $role_id);
            if ($stmt_insert->execute()) {
                $add_message = "Utilisateur ajouté avec succès.";
                $add_success = true;
            } else {
                $add_message = "Erreur lors de l'ajout de l'utilisateur : " . htmlspecialchars($stmt_insert->error);
            }
            $stmt_insert->close();
        }
        $stmt->close();
    }
}

// Traitement de la suppression d'un utilisateur
$delete_message = '';
$delete_success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_user') {
    $delete_user_id = intval($_POST['user_id']);

    // Empêcher la suppression de l'administrateur courant
    if ($delete_user_id === $_SESSION['user_id']) {
        $delete_message = "Vous ne pouvez pas supprimer votre propre compte.";
    } else {
        // Supprimer l'utilisateur
        $stmt_delete = $connection->prepare("DELETE FROM Utilisateur WHERE idUtilisateur = ?");
        $stmt_delete->bind_param("i", $delete_user_id);
        if ($stmt_delete->execute()) {
            $delete_message = "Utilisateur supprimé avec succès.";
            $delete_success = true;
        } else {
            $delete_message = "Erreur lors de la suppression de l'utilisateur : " . htmlspecialchars($stmt_delete->error);
        }
        $stmt_delete->close();
    }
}

// Récupérer la liste des utilisateurs
$stmt = $connection->prepare("
    SELECT u.idUtilisateur, u.Nom, u.Prenom, u.Email, r.Description AS Role
    FROM Utilisateur u
    JOIN Role r ON u.idRole = r.idRole
    ORDER BY u.idUtilisateur DESC
");
$stmt->execute();
$result = $stmt->get_result();
$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}
$stmt->close();

// Récupérer la liste des rôles pour le formulaire d'ajout
$stmt = $connection->prepare("SELECT idRole, Description FROM Role");
$stmt->execute();
$result = $stmt->get_result();
$roles = [];
while ($row = $result->fetch_assoc()) {
    $roles[] = $row;
}
$stmt->close();

$connection->close();
?>

<div class="container mt-5">
    <h1 class="mb-4">Gestion des utilisateurs</h1>

    <!-- Formulaire d'ajout d'utilisateur -->
    <div class="card mb-4">
        <div class="card-header">
            <h3>Ajouter un nouvel utilisateur</h3>
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
                <input type="hidden" name="action" value="add_user">
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="nom">Nom</label>
                        <input type="text" class="form-control" id="nom" name="nom" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="prenom">Prénom</label>
                        <input type="text" class="form-control" id="prenom" name="prenom" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="email">Adresse email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="role_id">Rôle</label>
                    <select class="form-control" id="role_id" name="role_id" required>
                        <option value="">Sélectionnez un rôle</option>
                        <?php foreach ($roles as $role): ?>
                            <option value="<?= htmlspecialchars($role['idRole']) ?>"><?= htmlspecialchars($role['Description']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Ajouter l'utilisateur</button>
            </form>
        </div>
    </div>

    <!-- Section de suppression d'utilisateur -->
    <?php if ($delete_message): ?>
        <div class="alert <?= $delete_success ? 'alert-success' : 'alert-danger' ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($delete_message) ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Fermer">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <!-- Table des utilisateurs -->
    <div class="card">
        <div class="card-header">
            <h3>Liste des utilisateurs</h3>
        </div>
        <div class="card-body">
            <?php if (count($users) === 0): ?>
                <p>Aucun utilisateur trouvé.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead class="thead-dark">
                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Prénom</th>
                            <th>Email</th>
                            <th>Rôle</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= htmlspecialchars($user['idUtilisateur']) ?></td>
                                <td><?= htmlspecialchars($user['Nom']) ?></td>
                                <td><?= htmlspecialchars($user['Prenom']) ?></td>
                                <td><?= htmlspecialchars($user['Email']) ?></td>
                                <td><?= htmlspecialchars($user['Role']) ?></td>
                                <td>
                                    <!-- Bouton d'édition -->
                                    <a href="edit_user.php?id=<?= htmlspecialchars($user['idUtilisateur']) ?>" class="btn btn-sm btn-warning">Éditer</a>

                                    <!-- Bouton de suppression -->
                                    <form method="post" action="" style="display:inline-block;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');">
                                        <input type="hidden" name="action" value="delete_user">
                                        <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['idUtilisateur']) ?>">
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
