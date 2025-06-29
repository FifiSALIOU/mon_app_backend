<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: connexion.php');
    exit;
}

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $content = trim($data['content'] ?? '');
    if ($content) {
        $stmt = $pdo->prepare("INSERT INTO messages (user_id, content) VALUES (?, ?)");
        $stmt->execute([$userId, $content]);
    }
}

$stmt = $pdo->query("SELECT m.content, m.created_at, u.name FROM messages m JOIN users u ON m.user_id = u.id ORDER BY m.created_at DESC");
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($messages);
?>
