<?php
header('Content-Type: application/json');
error_reporting(0); // Évite que des warnings polluent la réponse JSON
require 'config.php';

$data = $_POST;

if (!isset($data['user_id'], $data['prenom'], $data['nom'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Champs requis manquants']);
    exit;
}

$user_id = (int) $data['user_id'];
$prenom = trim($data['prenom']);
$nom = trim($data['nom']);
$password = isset($data['password']) ? $data['password'] : '';

try {
    if (!empty($password)) {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $db->prepare("UPDATE users SET prenom = :prenom, nom = :nom, password = :password WHERE id = :id");
        $stmt->execute([
            ':prenom' => $prenom,
            ':nom' => $nom,
            ':password' => $passwordHash,
            ':id' => $user_id
        ]);
    } else {
        $stmt = $db->prepare("UPDATE users SET prenom = :prenom, nom = :nom WHERE id = :id");
        $stmt->execute([
            ':prenom' => $prenom,
            ':nom' => $nom,
            ':id' => $user_id
        ]);
    }

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur serveur : ' . $e->getMessage()]);
}
