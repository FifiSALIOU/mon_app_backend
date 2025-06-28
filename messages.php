<?php
header('Content-Type: application/json');
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Non authentifié']);
    exit;
}

$user_id = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Récupérer messages où user_id est sender ou receiver
    $stmt = $pdo->prepare("SELECT m.*, u1.firstname AS sender_firstname, u2.firstname AS receiver_firstname
                           FROM messages m
                           JOIN users u1 ON m.sender_id = u1.id
                           JOIN users u2 ON m.receiver_id = u2.id
                           WHERE sender_id = ? OR receiver_id = ?
                           ORDER BY created_at DESC");
    $stmt->execute([$user_id, $user_id]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($messages);
} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $receiver_id = $data['receiver_id'] ?? null;
    $content = $data['content'] ?? null;

    if (!$receiver_id || !$content) {
        http_response_code(400);
        echo json_encode(['error' => 'Destinataire et contenu du message requis']);
        exit;
    }

    // Vérifier si le receiver_id existe
    $stmtCheckReceiver = $pdo->prepare("SELECT id FROM users WHERE id = ?");
    $stmtCheckReceiver->execute([$receiver_id]);
    if (!$stmtCheckReceiver->fetch()) {
        http_response_code(400);
        echo json_encode(['error' => 'Destinataire non trouvé']);
        exit;
    }


    $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, content) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $receiver_id, $content]);
    echo json_encode(['success' => 'Message envoyé avec succès']);
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Méthode non autorisée']);
}
?>