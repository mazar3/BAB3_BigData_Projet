<?php
global $connection;
include 'db_connect.php';

$sql = "SELECT * FROM test_table";
$result = $connection->query($sql);

if ($result->num_rows > 0) {
    echo "Données de test_table :<br>";
    while($row = $result->fetch_assoc()) {
        echo "ID : " . $row["id"] . " - Nom : " . $row["name"] . "<br>";
    }
} else {
    echo "Aucune donnée trouvée dans test_table.";
}

$connection->close();
?>