<?php
// contact.php
include '/header.php';
?>
<div class="container mt-5">
    <h1>Contactez-Nous</h1>
    <p>Vous avez des questions ou souhaitez en savoir plus sur nos services ? N'hésitez pas à nous contacter via les moyens suivants :</p>

    <ul class="list-unstyled">
        <li><strong>Email :</strong> <a href="mailto:contact@factodb.com" class="text-white">contact@factodb.com</a></li>
        <li><strong>Téléphone :</strong> +32 123 456 789</li>
        <li><strong>Adresse :</strong> Rue Exemple 123, 1000 Bruxelles, Belgique</li>
    </ul>

    <h3>Formulaire de Contact</h3>
    <form action="process_contact.php" method="post">
        <div class="form-group">
            <label for="name">Nom :</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>
        <div class="form-group">
            <label for="email">Email :</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <div class="form-group">
            <label for="message">Message :</label>
            <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Envoyer</button>
    </form>
</div>
<?php
include '/footer.php';
?>