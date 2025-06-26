<?php 
header('Content-Type: application/json');
require 'db.php'; // Assure-toi que ce fichier utilise bien pdo_pgsql (vu précédemment)

$data = json_decode(file_get_contents('php://input'), true);

// 🧾 Récupération sécurisée des données
$firstname = trim($data['firstname'] ?? '');
$lastname = trim($data['lastname'] ?? '');
$email = trim($data['email'] ?? '');
$phone = trim($data['phone'] ?? '');
$password = $data['password'] ?? '';
$password_confirm = $data['password_confirm'] ?? '';

// 📋 Vérification des champs requis
if (!$firstname || !$lastname || !$email || !$phone || !$password || !$password_confirm) {
    http_response_code(400);
    echo json_encode(['error' => 'Tous les champs sont obligatoires']);
    exit;
}

// 🔐 Vérification du mot de passe
if ($password !== $password_confirm) {
    http_response_code(400);
    echo json_encode(['error' => 'Les mots de passe ne correspondent pas']);
    exit;
}

// 🔑 Hachage sécurisé du mot de passe
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// 🗃️ Requête d'insertion PostgreSQL
$query = "INSERT INTO users (firstname, lastname, email, phone, password_hash, created_at)
          VALUES (:firstname, :lastname, :email, :phone, :password_hash, NOW())";

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        ':firstname' => $firstname,
        ':lastname' => $lastname,
        ':email' => $email,
        ':phone' => $phone,
        ':password_hash' => $password_hash
    ]);
    
    echo json_encode(['success' => 'Inscription réussie ✅']);
} catch (PDOException $e) {
    http_response_code(400);
    if (strpos($e->getMessage(), 'users_email_key') !== false) { // clé unique sur email
        echo json_encode(['error' => 'Email déjà utilisé']);
    } else {
        echo json_encode(['error' => 'Erreur lors de l\'inscription']);
    }
}
?>