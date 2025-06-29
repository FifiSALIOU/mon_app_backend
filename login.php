<?php
session_start();
require 'config.php';
$data = json_decode(file_get_contents("php://input"), true);

$email = trim($data['email'] ?? '');
$password = trim($data['password'] ?? '');

$stmt = $pdo->prepare("SELECT id, prenom, nom FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && password_verify($password, $pdo->query("SELECT password FROM users WHERE id={$user['id']}")->fetchColumn())) {
    $_SESSION['user_id'] = $user['id'];
    echo json_encode(['success' => true, 'prenom' => $user['prenom'], 'nom' => $user['nom']]);
} else {
    echo json_encode(['error' => 'Identifiants invalides']);
}
