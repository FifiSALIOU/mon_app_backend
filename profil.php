<?php
include 'config.php';
session_start();
if (!$_SESSION['user_id']) exit;
$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);
if (isset($data['prenom'], $data['nom'])) {
  $stmt = $db->prepare("UPDATE users SET prenom=?, nom=? WHERE id=?");
  $stmt->execute([$data['prenom'],$data['nom'],$user_id]);
}
if (isset($data['password'])) {
  $stmt = $db->prepare("UPDATE users SET password=? WHERE id=?");
  $stmt->execute([password_hash($data['password'], PASSWORD_BCRYPT),$user_id]);
}
echo json_encode(['success'=>true]);
