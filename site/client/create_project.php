<?php
// create_project.php
global $connection;
include '../header.php';
include '../db_connect.php';

// Vérifier si l'utilisateur est connecté et a le rôle "Client"
if (!isset($_SESSION['user_id']) || get_role() !== 'Client') {
    header("Location: ../login.php");
    exit();
}

$message = '';
$success = false;

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_project') {
    $nom_projet = trim($_POST['nom_projet']);
    $description = trim($_POST['description']);
    $user_id = $_SESSION['user_id'];

    // Vérifier les champs
    if (empty($nom_projet) || empty($description)) {
        $message = "Veuillez remplir tous les champs.";
    } else {
        // Insertion du projet dans la base
        // Statut = "En cours de validation"
        // Date_Debut = CURDATE()
        $stmt = $connection->prepare("
            INSERT INTO Projet (Nom, Description, Date_Debut, Statut, idUtilisateur)
            VALUES (?, ?, CURDATE(), 'En cours de validation', ?)
        ");
        if ($stmt) {
            $stmt->bind_param("ssi", $nom_projet, $description, $user_id);
            if ($stmt->execute()) {
                $message = "Votre projet a été créé avec succès et est en cours de validation par un administrateur.";
                $success = true;
            } else {
                $message = "Erreur lors de la création du projet : " . htmlspecialchars($stmt->error);
            }
            $stmt->close();
        } else {
            $message = "Erreur lors de la préparation de la requête : " . htmlspecialchars($connection->error);
        }
    }
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

    <?php if ($success): ?>
        <!-- Si le projet est créé avec succès, on affiche un lien retour vers dashboard_client.php -->
        <div class="mt-4">
            <a href="dashboard_client.php" class="btn btn-primary">Retour à votre tableau de bord</a>
        </div>
    <?php else: ?>
        <div class="card">
            <div class="card-header">
                <h2>Créer un nouveau projet</h2>
            </div>
            <div class="card-body">
                <form method="post" action="">
                    <input type="hidden" name="action" value="create_project">
                    <div class="form-group">
                        <label for="nom_projet">Nom du projet</label>
                        <input type="text" class="form-control" id="nom_projet" name="nom_projet" required>
                    </div>
                    <div class="form-group">
                        <label for="description">Description du projet</label>
                        <textarea class="form-control" id="description" name="description" rows="6" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-success">Créer le projet</button>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include '../footer.php'; ?>