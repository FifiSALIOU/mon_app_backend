<?php
require 'config.php';
$data = json_decode(file_get_contents("php://input"), true);

$prenom   = trim($data['prenom'] ?? '');
$nom      = trim($data['nom'] ?? '');
$tel      = trim($data['telephone'] ?? '');
$email    = trim($data['email'] ?? '');
$password = trim($data['password'] ?? '');

if (!$prenom || !$nom || !$tel || !$email || !$password) {
    http_response_code(400);
    echo json_encode(['error' => 'Champs manquants']);
    exit;
}

$hash = password_hash($password, PASSWORD_BCRYPT);
$stmt = $pdo->prepare("INSERT INTO users (prenom, nom, telephone, email, password) VALUES (?, ?, ?, ?, ?)");
try {
    $stmt->execute([$prenom, $nom, $tel, $email, $hash]);
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Email déjà utilisé']);
}
