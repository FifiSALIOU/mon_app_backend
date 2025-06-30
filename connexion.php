<?php
include 'config.php';
$data = json_decode(file_get_contents('php://input'), true);
$stmt = $db->prepare("SELECT id,prenom,nom,password FROM users WHERE email = ?");
$stmt->execute([$data['email']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if ($user && password_verify($data['password'], $user['password'])) {
    session_start();
    $_SESSION['user_id'] = $user['id'];
    echo json_encode(['success'=>true,'prenom'=>$user['prenom'],'nom'=>$user['nom']]);
} else echo json_encode(['success'=>false]);
