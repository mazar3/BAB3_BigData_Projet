<?php
// admin/edit_user.php
global $connection;
include '../header.php';
include '../db_connect.php';

// Vérifier si l'utilisateur est connecté et a le rôle "Administrateur"
if (!isset($_SESSION['user_id']) || get_role() !== 'Administrateur') {
    header("Location: ../login.php");
    exit();
}

// Vérifier si l'ID de l'utilisateur à éditer est fourni
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: manage_users.php");
    exit();
}

$user_id = intval($_GET['id']);

// Initialiser les variables
$edit_message = '';
$edit_success = false;

// Récupérer les détails de l'utilisateur à éditer
$stmt = $connection->prepare("
    SELECT u.Nom, u.Prenom, u.Email, u.idRole
    FROM Utilisateur u
    WHERE u.idUtilisateur = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($nom, $prenom, $email, $current_role_id);
if (!$stmt->fetch()) {
    // Utilisateur non trouvé
    $stmt->close();
    echo '<div class="container mt-5"><div class="alert alert-danger">Utilisateur non trouvé.</div></div>';
    include '../footer.php';
    exit();
}
$stmt->close();

// Récupérer la liste des rôles pour le formulaire
$stmt = $connection->prepare("SELECT idRole, Description FROM Role");
$stmt->execute();
$result = $stmt->get_result();
$roles = [];
while ($row = $result->fetch_assoc()) {
    $roles[] = $row;
}
$stmt->close();

// Traitement du formulaire d'édition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_user') {
    $new_nom = trim($_POST['nom']);
    $new_prenom = trim($_POST['prenom']);
    $new_email = trim($_POST['email']);
    $new_password = trim($_POST['password']);
    $new_role_id = intval($_POST['role_id']);

    // Validation des champs
    if (empty($new_nom) || empty($new_prenom) || empty($new_email) || empty($new_role_id)) {
        $edit_message = "Veuillez remplir tous les champs obligatoires.";
    } elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $edit_message = "L'adresse email n'est pas valide.";
    } else {
        // Vérifier si l'email existe déjà pour un autre utilisateur
        $stmt = $connection->prepare("SELECT idUtilisateur FROM Utilisateur WHERE Email = ? AND idUtilisateur != ?");
        $stmt->bind_param("si", $new_email, $user_id);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $edit_message = "Cet email est déjà utilisé par un autre utilisateur.";
        } else {
            $stmt->close();
            // Mettre à jour les détails de l'utilisateur
            if (!empty($new_password)) {
                // Si un nouveau mot de passe est fourni, le hacher
                $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
                $stmt_update = $connection->prepare("
                    UPDATE Utilisateur 
                    SET Nom = ?, Prenom = ?, Email = ?, Password = ?, idRole = ?
                    WHERE idUtilisateur = ?
                ");
                $stmt_update->bind_param("ssssii", $new_nom, $new_prenom, $new_email, $hashed_password, $new_role_id, $user_id);
            } else {
                // Sinon, ne pas mettre à jour le mot de passe
                $stmt_update = $connection->prepare("
                    UPDATE Utilisateur 
                    SET Nom = ?, Prenom = ?, Email = ?, idRole = ?
                    WHERE idUtilisateur = ?
                ");
                $stmt_update->bind_param("sssii", $new_nom, $new_prenom, $new_email, $new_role_id, $user_id);
            }

            if ($stmt_update->execute()) {
                $edit_message = "Utilisateur mis à jour avec succès.";
                $edit_success = true;

                // Mettre à jour les variables avec les nouvelles valeurs
                $nom = $new_nom;
                $prenom = $new_prenom;
                $email = $new_email;
                $current_role_id = $new_role_id;
            } else {
                $edit_message = "Erreur lors de la mise à jour de l'utilisateur : " . htmlspecialchars($stmt_update->error);
            }
            $stmt_update->close();
        }
    }
}

$connection->close();
?>

<div class="container mt-5">
    <h1 class="mb-4">Éditer l'utilisateur</h1>

    <div class="card">
        <div class="card-header">
            <h3>Modifier les Détails de l'utilisateur</h3>
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
                <input type="hidden" name="action" value="edit_user">
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="nom">Nom</label>
                        <input type="text" class="form-control" id="nom" name="nom" value="<?= htmlspecialchars($nom) ?>" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="prenom">Prénom</label>
                        <input type="text" class="form-control" id="prenom" name="prenom" value="<?= htmlspecialchars($prenom) ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="email">Adresse email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required>
                </div>
                <div class="form-group">
                    <label for="password">Mot de passe <small>(Laisser vide pour ne pas changer)</small></label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Nouveau mot de passe">
                </div>
                <div class="form-group">
                    <label for="role_id">Rôle</label>
                    <select class="form-control" id="role_id" name="role_id" required>
                        <option value="">Sélectionnez un rôle</option>
                        <?php foreach ($roles as $role): ?>
                            <option value="<?= htmlspecialchars($role['idRole']) ?>" <?= ($role['idRole'] == $current_role_id) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($role['Description']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Mettre à Jour l'utilisateur</button>
                <a href="manage_users.php" class="btn btn-secondary">Retour à la gestion</a>
            </form>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?>
