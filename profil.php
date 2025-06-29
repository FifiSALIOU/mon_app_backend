<?php
session_start();
require 'config.php';
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$prenom = trim($data['prenom'] ?? '');
$nom    = trim($data['nom'] ?? '');
$newpass = trim($data['password'] ?? '');
$uid = $_SESSION['user_id'];

if ($prenom && $nom) {
    $pdo->prepare("UPDATE users SET prenom=?, nom=? WHERE id=?")->execute([$prenom, $nom, $uid]);
}

if ($newpass) {
    $hash = password_hash($newpass, PASSWORD_BCRYPT);
    $pdo->prepare("UPDATE users SET password=? WHERE id=?")->execute([$hash, $uid]);
}

echo json_encode(['success' => true]);
