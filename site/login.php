<?php
global $connection;
session_start();
include 'db_connect.php';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $connection->prepare("
        SELECT u.idUtilisateur, u.Mot_De_Passe_Hash, r.idRole, r.Description
        FROM Utilisateur u
        JOIN Role r ON u.idRole = r.idRole
        WHERE u.Email = ?
    ");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($user_id, $passwordHash, $role_id, $role_description);
        $stmt->fetch();

        if (password_verify($password, $passwordHash)) {
            $_SESSION['user_id'] = $user_id;
            $_SESSION['email'] = $email;
            $_SESSION['idRole'] = $role_id;
            $_SESSION['role_description'] = $role_description;
            header("Location: redirect_role.php");
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
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <?php if ($message): ?>
        <div class="alert alert-danger" role="alert">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

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
    <p class="mt-3">Pas encore de compte ? <a href="register.php">Inscrivez-vous ici</a></p>
</div>
</body>
</html>
