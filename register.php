<?php
require 'config.php';

$data = json_decode(file_get_contents("php://input"), true);
$email = trim($data['email'] ?? '');
$password = trim($data['password'] ?? '');
$name = trim($data['name'] ?? '');

if (!$email || !$password) {
    http_response_code(400);
    echo json_encode(["error" => "Champs manquants"]);
    exit;
}

$hashedPassword = password_hash($password, PASSWORD_BCRYPT);
$stmt = $pdo->prepare("INSERT INTO users (email, password, name) VALUES (?, ?, ?)");
try {
    $stmt->execute([$email, $hashedPassword, $name]);
    echo json_encode(["success" => true]);
} catch (PDOException $e) {
    echo json_encode(["error" => "Email déjà utilisé"]);
}
?>
