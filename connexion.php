<?php
header('Content-Type: application/json');
require 'config.php';

$data = $_POST;

if (!isset($data['email'], $data['password'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Email et mot de passe requis']);
    exit;
}

$email = trim($data['email']);
$password = $data['password'];

$stmt = $db->prepare("SELECT id, prenom, nom, password FROM users WHERE email = :email");
$stmt->execute([':email' => $email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && password_verify($password, $user['password'])) {
    session_start();
    $_SESSION['user_id'] = $user['id'];
    echo json_encode([
        'success' => true,
        'prenom' => $user['prenom'],
        'nom' => $user['nom']
    ]);
} else {
    http_response_code(401);
    echo json_encode(['error' => 'Identifiants incorrects']);
}
