<?php
header('Content-Type: application/json');
require 'db.php';

$data = json_decode(file_get_contents('php://input'), true);

$firstname = $data['firstname'] ?? '';
$lastname = $data['lastname'] ?? '';
$email = $data['email'] ?? '';
$phone = $data['phone'] ?? '';
$password = $data['password'] ?? '';
$password_confirm = $data['password_confirm'] ?? '';

if (!$firstname || !$lastname || !$email || !$phone || !$password || !$password_confirm) {
    http_response_code(400);
    echo json_encode(['error' => 'Tous les champs sont obligatoires']);
    exit;
}

if ($password !== $password_confirm) {
    http_response_code(400);
    echo json_encode(['error' => 'Les mots de passe ne correspondent pas']);
    exit;
}

$password_hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("INSERT INTO users (firstname, lastname, email, phone, password_hash) VALUES (?, ?, ?, ?, ?)");
try {
    $stmt->execute([$firstname, $lastname, $email, $phone, $password_hash]);
    echo json_encode(['success' => 'Inscription réussie']);
} catch (PDOException $e) {
    http_response_code(400);
    if (strpos($e->getMessage(), 'email') !== false) {
        echo json_encode(['error' => 'Email déjà utilisé']);
    } else {
        echo json_encode(['error' => 'Erreur lors de l\'inscription']);
    }
}
