<?php
global $connection;
session_start();
include 'db_connect.php';
$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'register')
{
    // Processus d'inscription
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];
    $nom = $_POST['Nom']; // Correspondance avec le nom du champ du formulaire
    $prenom = $_POST['Prenom']; // Correspondance avec le nom du champ du formulaire
    $telephone = $_POST['tel'];

    // Vérifier que les mots de passe correspondent
    if ($password !== $confirmPassword)
    {
        $message = "Les mots de passe ne correspondent pas.";
    }
    else
    {
        // Vérifier si l'email existe déjà
        $stmt = $connection->prepare("SELECT idUtilisateur FROM utilisateur WHERE Email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0)
        {
            // Si l'email existe déjà
            $message = "Cette adresse email est déjà utilisée.";
        }
        else
        {
            // Si l'email n'existe pas, insérer les nouvelles données
            $passwordHash = password_hash($password, PASSWORD_BCRYPT);

            // Préparer l'insertion de toutes les variables
            $stmt = $connection->prepare("INSERT INTO utilisateur (Email, MotDePasseHash, Nom, Prenom, Telephone) VALUES (?, ?, ?, ?, ?)");
            // Vous pouvez modifier les noms des colonnes ci-dessus selon votre table SQL

            // Lier les paramètres
            $stmt->bind_param("sssss", $email, $passwordHash, $nom, $prenom, $telephone);

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
        // Fonction pour empêcher l'entrée de caractères non numériques
        function validatePhoneInput(input) {
            input.value = input.value.replace(/[^0-9]/g, ''); // Remplace tout caractère non numérique par rien
        }
    </script>
    <p>Déjà inscrit ? <a href="index.php">Connectez-vous ici</a></p>
</div>
</body>
</html>