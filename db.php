<?php
// Connexion PDO à la base MariaDB
try {
    $pdo = new PDO('mysql:host=DB_HOST;dbname=DB_NAME;charset=utf8', 'DB_USER', 'DB_PASS');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die(json_encode(['error' => 'Erreur DB : ' . $e->getMessage()]));
}
