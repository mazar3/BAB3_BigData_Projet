<?php
global $connection;
session_start();
include 'db_connect.php';
$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'register')
{
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];
    $nom = $_POST['Nom'];
    $prenom = $_POST['Prenom'];
    $telephone = $_POST['tel'];

    if ($password !== $confirmPassword)
    {
        $message = "Les mots de passe ne correspondent pas.";
    }
    else
    {
        $stmt = $connection->prepare("SELECT idUtilisateur FROM utilisateur WHERE Email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0)
        {
            $message = "Cette adresse email est déjà utilisée.";
        }
        else
        {
            $passwordHash = password_hash($password, PASSWORD_BCRYPT);

            $defaultRoleId = 4;

            $stmt = $connection->prepare("
                INSERT INTO utilisateur (Email, Mot_De_Passe_Hash, Nom, Prenom, Telephone, idRole)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("sssssi", $email, $passwordHash, $nom, $prenom, $telephone, $defaultRoleId);

            if ($stmt->execute())
            {
                $message = "Inscription réussie. Vous pouvez maintenant vous connecter.";
            }
            else
            {
                $message = "Erreur lors de l'inscription : " . $stmt->error;
            }
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Inscription</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <?php if ($message): ?>
        <div class="alert alert-info" role="alert">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <h2>Inscription</h2>
    <form method="post" action="">
        <input type="hidden" name="action" value="register">
        <div class="form-group">
            <label for="emailRegister">Email</label>
            <input type="email" class="form-control" id="emailRegister" name="email" required value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
        </div>
        <div class="form-group">
            <label for="passwordRegister">Mot de passe</label>
            <input type="password" class="form-control" id="passwordRegister" name="password" required>
        </div>
        <div class="form-group">
            <label for="confirmPassword">Confirmer mot de passe</label>
            <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required>
        </div>
        <div class="form-group">
            <label for="prenomRegister">Prénom</label>
            <input type="text" class="form-control" id="prenomRegister" name="Prenom" required value="<?= isset($_POST['Prenom']) ? htmlspecialchars($_POST['Prenom']) : '' ?>">
        </div>
        <div class="form-group">
            <label for="nomRegister">Nom</label>
            <input type="text" class="form-control" id="nomRegister" name="Nom" required value="<?= isset($_POST['Nom']) ? htmlspecialchars($_POST['Nom']) : '' ?>">
        </div>
        <div class="form-group">
            <label for="telRegister">Téléphone</label>
            <input type="tel" class="form-control" id="telRegister" name="tel" oninput="validatePhoneInput(this)" required value="<?= isset($_POST['tel']) ? htmlspecialchars($_POST['tel']) : '' ?>">
        </div>
        <button type="submit" class="btn btn-success">S'inscrire</button>
    </form>

    <script>
        function validatePhoneInput(input) {
            input.value = input.value.replace(/[^0-9]/g, '');
        }
    </script>
    <p>Déjà inscrit ? <a href="login.php">Connectez-vous ici</a></p>
</div>
</body>
</html>