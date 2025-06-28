<?php
header('Content-Type: application/json');
require 'db.php';

// Lecture et débogage des données reçues
$data = json_decode(file_get_contents('php://input'), true);
file_put_contents('php://stderr', "Contenu reçu : " . file_get_contents('php://input') . "\n");
file_put_contents('php://stderr', "Data décodée : " . json_encode($data) . "\n");

// Récupération des champs
$identifiant = trim($data['identifiant'] ?? '');
$password = $data['password'] ?? '';

// Vérification des champs obligatoires
if (!$identifiant || !$password) {
    http_response_code(400);
    echo json_encode(['error' => 'Identifiant et mot de passe requis']);
    exit;
}

// Requête SQL : recherche par email ou téléphone
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? OR phone = ?");
$stmt->execute([$identifiant, $identifiant]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Vérification de l'utilisateur et du mot de passe
if (!$user || !password_verify($password, $user['password_hash'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Identifiants incorrects']);
    exit;
}

// Authentification réussie : on peut démarrer une session ou retourner un token
session_start();
$_SESSION['user_id'] = $user['id'];

echo json_encode([
    'success' => 'Connexion réussie',
    'user' => [
        'id' => $user['id'],
        'firstname' => $user['firstname'],
        'lastname' => $user['lastname'],
        'email' => $user['email'],
        'phone' => $user['phone']
    ]
]);
?>