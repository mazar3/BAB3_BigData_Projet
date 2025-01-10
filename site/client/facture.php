<?php
// admin/generate_invoice.php

global $connection;
require_once 'lib/dompdf/autoload.inc.php';

use Dompdf\Dompdf;

// Démarrer la session pour accéder aux données de l'utilisateur
session_start();

// Vérifier si l'utilisateur est connecté et a le rôle "Client"
if (!isset($_SESSION['user_id']) || $_SESSION['role_description'] !== 'Client') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Vérifier si idProjet est passé dans l'URL
if (!isset($_GET['idProjet']) || empty($_GET['idProjet'])) {
    header("Location: my_projects.php");
    exit();
}

$idProjet = intval($_GET['idProjet']);

// Connexion à la base de données
include '../db_connect.php';

// Récupérer les détails du projet
$stmt = $connection->prepare("
    SELECT p.Nom, p.Description, p.Statut, DATE_FORMAT(p.Date_Debut, '%d/%m/%Y') AS DateDebutFormat, p.idPanier
    FROM Projet p
    WHERE p.idProjet = ? AND p.idUtilisateur = ?
");
$stmt->bind_param("ii", $idProjet, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Projet non trouvé ou n'appartient pas à l'utilisateur
    echo '<div class="container mt-5"><div class="alert alert-danger">Projet non trouvé ou vous n\'avez pas l\'autorisation d\'y accéder.</div></div>';
    include '../footer.php';
    exit();
}

$project = $result->fetch_assoc();
$stmt->close();

$panier_id = $project['idPanier'];

// Vérifier que le devis a été accepté (statut "Terminé")
if ($project['Statut'] !== 'Terminé') {
    echo '<div class="container mt-5"><div class="alert alert-danger">La facture ne peut être générée que si le devis a été accepté.</div></div>';
    include '../footer.php';
    exit();
}

// Récupérer les produits du panier
$stmt = $connection->prepare("
    SELECT p.Nom, p.Prix, pp.Quantite
    FROM Panier_produit pp
    JOIN Produit p ON pp.idProduit = p.idProduit
    WHERE pp.idPanier = ?
");
$stmt->bind_param("i", $panier_id);
$stmt->execute();
$result = $stmt->get_result();
$panier = [];
$total_montant = 0;
while ($row = $result->fetch_assoc()) {
    $panier[] = $row;
    $total_montant += $row['Prix'] * $row['Quantite'];
}
$stmt->close();

$connection->close();

// Générer le contenu HTML pour le PDF
$html = '
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .header { text-align: center; }
        .details, .products { width: 100%; margin-top: 20px; }
        .products th, .products td { border: 1px solid #000; padding: 8px; text-align: left; }
        .products th { background-color: #f2f2f2; }
        .total { text-align: right; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Facture</h1>
    </div>
    <div class="details">
        <p><strong>Projet :</strong> ' . htmlspecialchars($project['Nom']) . '</p>
        <p><strong>Description :</strong> ' . nl2br(htmlspecialchars($project['Description'])) . '</p>
        <p><strong>Date de Début :</strong> ' . htmlspecialchars($project['DateDebutFormat']) . '</p>
    </div>
    <div class="products">
        <table width="100%">
            <thead>
                <tr>
                    <th>Nom du Produit</th>
                    <th>Prix Unitaire (€)</th>
                    <th>Quantité</th>
                    <th>Total (€)</th>
                </tr>
            </thead>
            <tbody>';
foreach ($panier as $item) {
    $total = $item['Prix'] * $item['Quantite'];
    $html .= '
                <tr>
                    <td>' . htmlspecialchars($item['Nom']) . '</td>
                    <td>' . number_format($item['Prix'], 2, ',', ' ') . '</td>
                    <td>' . htmlspecialchars($item['Quantite']) . '</td>
                    <td>' . number_format($total, 2, ',', ' ') . '</td>
                </tr>';
}
$html .= '
            </tbody>
        </table>
    </div>
    <div class="total">
        <h3>Total : ' . number_format($total_montant, 2, ',', ' ') . ' €</h3>
    </div>
    <div class="payment">
        <h4>Informations de Paiement</h4>
        <p>Veuillez effectuer le paiement par virement bancaire sur le compte suivant :</p>
        <ul>
            <li><strong>IBAN :</strong> BE76 1234 5678 9012</li>
            <li><strong>BIC :</strong> ABCD1234XXX</li>
        </ul>
        <p>Merci de votre confiance.</p>
    </div>
</body>
</html>
';

// Instancier Dompdf
$dompdf = new Dompdf();

// Charger le contenu HTML
$dompdf->loadHtml($html, 'UTF-8');

// (Optionnel) Définir la taille et l'orientation du papier
$dompdf->setPaper('A4', 'portrait');

// Rendre le HTML en PDF
$dompdf->render();

// Envoyer le PDF généré au navigateur pour téléchargement
$dompdf->stream('facture_projet_' . $idProjet . '.pdf', array("Attachment" => true));
exit();
?>
