<?php
header('Content-Type: application/json');
require 'config.php'; // Connexion à la base

// 🔄 Récupérer le corps brut JSON si dispo
$input = json_decode(file_get_contents("php://input"), true);

// 🌐 Alternative : récupérer aussi POST si c’est un form classique
$data = $input ?: $_POST;

// 🛡 Vérification des champs
if (!isset($data["prenom"], $data["nom"], $data["email"], $data["password"])) {
    http_response_code(400);
    echo json_encode(["error" => "Champs requis manquants"]);
    exit;
}

$prenom = $data["prenom"];
$nom = $data["nom"];
$email = $data["email"];
$password = password_hash($data["password"], PASSWORD_DEFAULT);

try {
    $stmt = $db->prepare("INSERT INTO users (prenom, nom, email, password) VALUES (:prenom, :nom, :email, :password)");
    $stmt->execute([
        ':prenom' => $prenom,
        ':nom' => $nom,
        ':email' => $email,
        ':password' => $password
    ]);
    echo json_encode(["success" => true]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
