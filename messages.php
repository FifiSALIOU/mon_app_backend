<?php
include 'config.php';
session_start();
if (!$_SESSION['user_id']) exit;
$user_id = $_SESSION['user_id'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $data = json_decode(file_get_contents('php://input'), true);
  $stmt = $db->prepare("INSERT INTO messages (user_id, content) VALUES (?, ?)");
  $stmt->execute([$user_id, $data['content']]);
  echo json_encode(['success'=>true]);
} else {
  $stmt = $db->prepare("SELECT m.id, u.prenom, m.content, m.created_at FROM messages m JOIN users u ON m.user_id=u.id ORDER BY m.created_at DESC");
  $stmt->execute();
  echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
}
