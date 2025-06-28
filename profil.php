<?php
header('Content-Type: application/json');
require 'db.php';
session_start(); // Nécessaire pour accéder à $_SESSION['user_id']

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Non authentifié']);
    exit;
}

// Récupération des données JSON envoyées par l'application Android
$data = json_decode(file_get_contents('php://input'), true);

// Débogage des données reçues (utile pour Render.com logs)
file_put_contents('php://stderr', "Contenu reçu profil: " . file_get_contents('php://input') . "\n");
file_put_contents('php://stderr', "Data décodée profil: " . json_encode($data) . "\n");

$firstname = trim($data['firstname'] ?? null);
$lastname = trim($data['lastname'] ?? null);
$phone = trim($data['phone'] ?? null);
$password = $data['password'] ?? null;
$password_confirm = $data['password_confirm'] ?? null;

$user_id = $_SESSION['user_id']; // ID de l'utilisateur authentifié

// Validation des données
if ($password && $password !== $password_confirm) {
    http_response_code(400);
    echo json_encode(['error' => 'Les mots de passe ne correspondent pas']);
    exit;
}
if ($password && strlen($password) < 8) {
    http_response_code(400);
    echo json_encode(['error' => 'Le nouveau mot de passe doit contenir au moins 8 caractères']);
    exit;
}

try {
    $updateFields = [];
    $updateValues = [];

    if ($firstname !== null) {
        $updateFields[] = "firstname = ?";
        $updateValues[] = $firstname;
    }
    if ($lastname !== null) {
        $updateFields[] = "lastname = ?";
        $updateValues[] = $lastname;
    }
    if ($phone !== null) {
        // Validation du téléphone si présent
        if (!preg_match('/^\+?[0-9]{9,15}$/', $phone)) {
            http_response_code(400);
            echo json_encode(['error' => 'Numéro de téléphone invalide. Format attendu : +221781234567']);
            exit;
        }
        $updateFields[] = "phone = ?";
        $updateValues[] = $phone;
    }
    if ($password) { // Seulement si un nouveau mot de passe est fourni et validé
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $updateFields[] = "password_hash = ?";
        $updateValues[] = $password_hash;
    }

    if (empty($updateFields)) {
        http_response_code(400);
        echo json_encode(['error' => 'Aucune donnée à mettre à jour']);
        exit;
    }

    $query = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE id = ?";
    $updateValues[] = $user_id; // Ajouter l'ID de l'utilisateur à la fin des valeurs

    $stmt = $pdo->prepare($query);
    $stmt->execute($updateValues);

    echo json_encode(['success' => 'Profil mis à jour avec succès']);

} catch (PDOException $e) {
    // Gestion des erreurs de base de données
    http_response_code(500);
    echo json_encode(['error' => 'Erreur lors de la mise à jour du profil', 'debug' => $e->getMessage()]);
}
?>