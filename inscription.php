<?php
header('Content-Type: application/json');
require 'config.php';

// 🔍 Étape 1 : lire le corps brut de la requête
$raw = file_get_contents("php://input");

// 🔍 Étape 2 : essayer de le décoder
$data = json_decode($raw, true);

// 🔧 Afficher les infos reçues pour debug (temporaire — à retirer ensuite)
if (!$data) {
    echo json_encode([
        "error" => "JSON invalide ou vide",
        "debug_raw" => $raw
    ]);
    exit;
}

// 🛡 Vérification des champs obligatoires
if (!isset($data["prenom"], $data["nom"], $data["email"], $data["password"])) {
    echo json_encode([
        "error" => "Champs requis manquants",
        "reçu" => $data
    ]);
    exit;
}

// ✅ Récupération des données
$prenom = $data["prenom"];
$nom = $data["nom"];
$email = $data["email"];
$password = password_hash($data["password"], PASSWORD_DEFAULT);

// 🔄 Insertion dans la base
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
    echo json_encode(["error" => $e->getMessage()]);
}
