<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "bab3_bigdata_projet";

$connection = new mysqli($servername, $username, $password, $dbname);

if ($connection->connect_error) {
    die("La connexion a échoué : " . $connection->connect_error);
}
?>