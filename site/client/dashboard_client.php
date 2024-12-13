<?php
// client/dashboard_client.php
global $connection;
include '../header.php';
include '../db_connect.php';

// Vérifier si l'utilisateur est connecté et a le rôle "Client"
if (!isset($_SESSION['user_id']) || get_role() !== 'Client') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Récupérer les informations de l'utilisateur pour affichage
$stmt = $connection->prepare("SELECT Nom, Prenom FROM Utilisateur WHERE idUtilisateur = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($nom, $prenom);
$stmt->fetch();
$stmt->close();
$connection->close();
?>

<div class="container mt-5">
    <div class="jumbotron">
        <h1 class="display-4">Bienvenue, <?= htmlspecialchars($prenom . ' ' . $nom); ?>!</h1>
        <p class="lead">Accédez à vos services et fonctionnalités personnalisées ci-dessous.</p>
        <hr class="my-4">
        <p>Vous pouvez consulter vos projets ou en créer un nouveau.</p>
        <a class="btn btn-primary btn-lg" href="my_projects.php" role="button">Consulter mes projets</a>
        <a class="btn btn-success btn-lg" href="create_project.php" role="button">Créer un projet</a>
    </div>
</div>

<?php include '../footer.php'; ?>
