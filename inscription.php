<?php
include 'config.php';
$data = json_decode(file_get_contents('php://input'), true);
$stmt = $db->prepare("INSERT INTO users (prenom, nom, email, password) VALUES (?, ?, ?, ?)");
$stmt->execute([$data['prenom'],$data['nom'],$data['email'],password_hash($data['password'], PASSWORD_BCRYPT)]);
echo json_encode(['success' => true]);
