<?php
// admin/edit_project.php
global $connection;
include '../header.php';
include '../db_connect.php';

// Vérifier si l'utilisateur est connecté et a le rôle "Administrateur"
if (!isset($_SESSION['user_id']) || get_role() !== 'Administrateur') {
    header("Location: ../login.php");
    exit();
}

// Vérifier si l'ID du projet à éditer est fourni
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: manage_projects.php");
    exit();
}

$project_id = intval($_GET['id']);

// Initialiser les variables
$edit_message = '';
$edit_success = false;

// Récupérer les détails du projet
$stmt = $connection->prepare("
    SELECT p.Nom, p.Description, p.Statut, DATE_FORMAT(p.Date_Debut, '%Y-%m-%d') AS DateDebut, p.idUtilisateur, u.Nom AS ClientNom, u.Prenom AS ClientPrenom
    FROM Projet p
    JOIN Utilisateur u ON p.idUtilisateur = u.idUtilisateur
    WHERE p.idProjet = ?
");
$stmt->bind_param("i", $project_id);
$stmt->execute();
$stmt->bind_result($nom, $description, $statut, $date_debut, $client_id, $client_nom, $client_prenom);
if (!$stmt->fetch()) {
    // Projet non trouvé
    $stmt->close();
    echo '<div class="container mt-5"><div class="alert alert-danger">Projet non trouvé.</div></div>';
    include '../footer.php';
    exit();
}
$stmt->close();

// Récupérer les responsables actuels du projet
$stmt = $connection->prepare("
    SELECT pm.idUtilisateur, u.Prenom, u.Nom
    FROM Projet_manager pm
    JOIN Utilisateur u ON pm.idUtilisateur = u.idUtilisateur
    JOIN Role r ON u.idRole = r.idRole
    WHERE pm.idProjet = ? AND r.Description = 'Responsable de projet'
");
$stmt->bind_param("i", $project_id);
$stmt->execute();
$result = $stmt->get_result();
$current_managers = [];
while ($row = $result->fetch_assoc()) {
    $current_managers[] = $row['idUtilisateur'];
}
$stmt->close();

// Récupérer la liste des responsables disponibles pour l'assignation
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

// Traitement du formulaire d'édition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_project') {
    $new_nom = trim($_POST['nom']);
    $new_description = trim($_POST['description']);
    $new_statut = trim($_POST['statut']);
    $new_date_debut = trim($_POST['date_debut']);
    $new_managers = isset($_POST['managers']) ? $_POST['managers'] : [];

    // Validation des champs
    if (empty($new_nom) || empty($new_description) || empty($new_statut) || empty($new_date_debut)) {
        $edit_message = "Veuillez remplir tous les champs obligatoires.";
    } elseif (!DateTime::createFromFormat('Y-m-d', $new_date_debut)) {
        $edit_message = "La date de début n'est pas valide.";
    } else {
        // Mettre à jour les détails du projet
        $stmt = $connection->prepare("
            UPDATE Projet 
            SET Nom = ?, Description = ?, Statut = ?, Date_Debut = ?
            WHERE idProjet = ?
        ");
        $stmt->bind_param("ssssi", $new_nom, $new_description, $new_statut, $new_date_debut, $project_id);
        if ($stmt->execute()) {
            $stmt->close();

            // Mettre à jour les responsables
            // Supprimer les anciens responsables
            $stmt = $connection->prepare("DELETE FROM Projet_manager WHERE idProjet = ?");
            $stmt->bind_param("i", $project_id);
            if ($stmt->execute()) {
                $stmt->close();

                // Insérer les nouveaux responsables
                if (!empty($new_managers)) {
                    $stmt_insert = $connection->prepare("INSERT INTO Projet_manager (idProjet, idUtilisateur) VALUES (?, ?)");
                    foreach ($new_managers as $manager_id) {
                        $manager_id = intval($manager_id);
                        $stmt_insert->bind_param("ii", $project_id, $manager_id);
                        $stmt_insert->execute();
                    }
                    $stmt_insert->close();
                }

                $edit_message = "Projet mis à jour avec succès.";
                $edit_success = true;

                // Mettre à jour les variables avec les nouvelles valeurs
                $nom = $new_nom;
                $description = $new_description;
                $statut = $new_statut;
                $date_debut = $new_date_debut;
                $current_managers = $new_managers;
            } else {
                $edit_message = "Erreur lors de la mise à jour des responsables : " . htmlspecialchars($stmt->error);
                $stmt->close();
            }
        } else {
            $edit_message = "Erreur lors de la mise à jour du projet : " . htmlspecialchars($stmt->error);
            $stmt->close();
        }
    }
}

// Récupérer à nouveau les responsables pour l'affichage
$stmt = $connection->prepare("
    SELECT pm.idUtilisateur, u.Prenom, u.Nom
    FROM Projet_manager pm
    JOIN Utilisateur u ON pm.idUtilisateur = u.idUtilisateur
    JOIN Role r ON u.idRole = r.idRole
    WHERE pm.idProjet = ? AND r.Description = 'Responsable de projet'
");
$stmt->bind_param("i", $project_id);
$stmt->execute();
$result = $stmt->get_result();
$current_managers_details = [];
while ($row = $result->fetch_assoc()) {
    $current_managers_details[] = $row;
}
$stmt->close();

$connection->close();
?>

<div class="container mt-5">
    <h1 class="mb-4">Éditer le projet</h1>

    <div class="card">
        <div class="card-header">
            <h3>Modifier les détails du projet</h3>
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
                <input type="hidden" name="action" value="edit_project">

                <div class="form-group">
                    <label for="nom">Nom du projet</label>
                    <input type="text" class="form-control" id="nom" name="nom" value="<?= htmlspecialchars($nom) ?>" required>
                </div>

                <div class="form-group">
                    <label for="description">Description du projet</label>
                    <textarea class="form-control" id="description" name="description" rows="5" required><?= htmlspecialchars($description) ?></textarea>
                </div>

                <div class="form-group">
                    <label for="statut">Statut du projet</label>
                    <select class="form-control" id="statut" name="statut" required>
                        <option value="En cours de validation" <?= ($statut === 'En cours de validation') ? 'selected' : '' ?>>En cours de validation</option>
                        <option value="Validé" <?= ($statut === 'Validé') ? 'selected' : '' ?>>Validé</option>
                        <option value="Rejeté" <?= ($statut === 'Rejeté') ? 'selected' : '' ?>>Rejeté</option>
                        <!-- Ajoutez d'autres statuts si nécessaire -->
                    </select>
                </div>

                <div class="form-group">
                    <label for="date_debut">Date de début</label>
                    <input type="date" class="form-control" id="date_debut" name="date_debut" value="<?= htmlspecialchars($date_debut) ?>" required>
                </div>

                <div class="form-group">
                    <label for="managers">Assignation des responsables</label>
                    <select multiple class="form-control" id="managers" name="managers[]">
                        <?php foreach ($available_managers as $manager): ?>
                            <option value="<?= htmlspecialchars($manager['idUtilisateur']) ?>" <?= in_array($manager['idUtilisateur'], $current_managers) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($manager['Prenom'] . ' ' . $manager['Nom']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="form-text text-muted">Maintenez la touche Ctrl (Windows) ou Commande (Mac) pour sélectionner plusieurs responsables.</small>
                </div>

                <button type="submit" class="btn btn-primary">Mettre à jour le projet</button>
                <a href="manage_projects.php" class="btn btn-secondary">Retour à la gestion des projets</a>
            </form>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?>
