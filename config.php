<?php
$host = "localhost";
$dbname = "mon_app_db";
$user = "root"; // ton identifiant MySQL
$pass = "";     // ton mot de passe MySQL

$pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
?>
