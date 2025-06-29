<?php
session_start();
require 'config.php';
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit;
}
$uid = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $content = trim($data['content'] ?? '');
    if ($content) {
        $stmt = $pdo->prepare("INSERT INTO messages (user_id, content) VALUES (?, ?)");
        $stmt->execute([$uid, $content]);
    }
}

$stmt = $pdo->query("
    SELECT u.prenom, u.nom, m.content, m.created_at
    FROM messages m JOIN users u ON m.user_id = u.id
    ORDER BY m.created_at DESC
");
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
