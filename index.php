<?php
global $connection;
session_start();
include 'db_connect.php';
$message = '';


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'login') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $connection->prepare("SELECT idUtilisateur, MotDePasseHash FROM utilisateur WHERE Email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($user_id, $passwordHash);
        $stmt->fetch();

        if (password_verify($password, $passwordHash)) {
            $_SESSION['user_id'] = $user_id;
            $_SESSION['email'] = $email;
            header("Location: dashboard.php");
            exit();
        } else {
            $message = "Email ou mot de passe incorrect.";
        }
    } else {
        $message = "Email ou mot de passe incorrect.";
    }
    $stmt->close();
}
$connection->close();
?>


<!DOCTYPE html>
<html>
<head>
    <title>Connexion ou Inscription</title>
    <!-- Inclusion de Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <?php if ($message): ?>
        <div class="alert alert-info" role="alert">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Formulaire de connexion -->
        <div class="col-md-6">
            <h2>Connexion</h2>
            <form method="post" action="">
                <input type="hidden" name="action" value="login">
                <div class="form-group">
                    <label for="emailLogin">Email</label>
                    <input type="email" class="form-control" id="emailLogin" name="email" required>
                </div>
                <div class="form-group">
                    <label for="passwordLogin">Mot de passe</label>
                    <input type="password" class="form-control" id="passwordLogin" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary">Se connecter</button>
            </form>
            <p>Pas encore de compte ? <a href="register.php">Inscrivez-vous ici</a></p>
        </div>
    </div>
</div>
<!-- Inclusion de Bootstrap JS et ses dépendances -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
