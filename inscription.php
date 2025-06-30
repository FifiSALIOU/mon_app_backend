<?php
header('Content-Type: application/json');
require 'config.php';

$data = $_POST;

if (!isset($data["prenom"], $data["nom"], $data["email"], $data["password"])) {
    http_response_code(400);
    echo json_encode(["error" => "Champs requis manquants"]);
    exit;
}

$prenom = trim($data["prenom"]);
$nom = trim($data["nom"]);
$email = trim($data["email"]);
$password = $data["password"];

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(["error" => "Email invalide"]);
    exit;
}

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

try {
    $stmt = $db->prepare("INSERT INTO users (prenom, nom, email, password) VALUES (:prenom, :nom, :email, :password)");
    $stmt->execute([
        ':prenom' => $prenom,
        ':nom' => $nom,
        ':email' => $email,
        ':password' => $hashedPassword
    ]);
    echo json_encode(["success" => true]);
} catch (PDOException $e) {
    http_response_code(500);
    // Gestion d'erreur spécifique (ex: email déjà existant)
    if (strpos($e->getMessage(), 'duplicate') !== false) {
        echo json_encode(["error" => "Email déjà utilisé"]);
    } else {
        echo json_encode(["error" => $e->getMessage()]);
    }
}
