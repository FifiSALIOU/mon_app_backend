<?php
header('Content-Type: application/json');
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Non authentifié']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$firstname = $data['firstname'] ?? null;
$lastname = $data['lastname'] ?? null;
$phone = $data['phone'] ?? null;
$password = $data['password'] ?? null;
$password_confirm = $data['password_confirm'] ?? null;

$user_id = $_SESSION['user_id'];

if ($password && $password !== $password_confirm) {
    http_response_code(400);
    echo json_encode(['error' => 'Les mots de passe ne correspondent pas']);
    exit;
}

try {
    if ($password) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET firstname = ?, lastname = ?, phone = ?, password_hash = ? WHERE id = ?");
        $stmt->execute([$firstname, $lastname, $phone, $password_hash, $user_id]);
    } else {
        $stmt = $pdo->prepare("UPDATE users SET firstname = ?, lastname = ?, phone = ? WHERE id = ?");
        $stmt->execute([$firstname, $lastname, $phone, $user_id]);
    }
    echo json_encode(['success' => 'Profil mis à jour']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur lors de la mise à jour']);
}
