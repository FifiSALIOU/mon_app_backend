<?php
header('Content-Type: application/json');
require 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Utilisateur non connecté']);
    exit;
}

$user_id = $_SESSION['user_id'];
$data = $_POST;

try {
    if (isset($data['prenom'], $data['nom'])) {
        $stmt = $db->prepare("UPDATE users SET prenom = :prenom, nom = :nom WHERE id = :id");
        $stmt->execute([
            ':prenom' => trim($data['prenom']),
            ':nom' => trim($data['nom']),
            ':id' => $user_id
        ]);
    }

    if (isset($data['password'])) {
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        $stmt = $db->prepare("UPDATE users SET password = :password WHERE id = :id");
        $stmt->execute([
            ':password' => $hashedPassword,
            ':id' => $user_id
        ]);
    }

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
