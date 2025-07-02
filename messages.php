<?php
header('Content-Type: application/json');
require 'config.php';
session_start();

$data = $_POST;

// ✅ Si session vide, on prend le user_id depuis le POST
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} elseif (isset($data['user_id'])) {
    $user_id = (int) $data['user_id'];
} else {
    http_response_code(401);
    echo json_encode(['error' => 'Utilisateur non connecté']);
    exit;
}


$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = $_POST;
    if (!isset($data['content']) || trim($data['content']) === '') {
        http_response_code(400);
        echo json_encode(['error' => 'Message vide']);
        exit;
    }

    try {
        $stmt = $db->prepare("INSERT INTO messages (user_id, content) VALUES (:user_id, :content)");
        $stmt->execute([
            ':user_id' => $user_id,
            ':content' => trim($data['content'])
        ]);
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $stmt = $db->prepare("SELECT m.id, m.content, m.created_at, u.prenom, u.nom FROM messages m JOIN users u ON m.user_id = u.id ORDER BY m.created_at DESC");
        $stmt->execute();
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'messages' => $messages]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Méthode non autorisée']);
}
