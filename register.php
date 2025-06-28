<?php
// Headers CORS pour permettre les requêtes depuis l'app Android
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept');
header('Content-Type: application/json; charset=utf-8');

// Gestion des requêtes OPTIONS (preflight CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require 'db.php'; // Fichier de connexion à la base de données

// Gestion multi-format des données (JSON ou form-urlencoded)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

    if (strpos($contentType, 'application/json') !== false) {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        
        // Vérifier si le JSON est valide
        if (json_last_error() !== JSON_ERROR_NONE) {
            http_response_code(400);
            echo json_encode(['error' => 'Format JSON invalide']);
            exit;
        }
    } else {
        $data = $_POST;
    }
} else {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['error' => 'Méthode non autorisée']);
    exit;
}

// Récupération sécurisée des données
$firstname = trim($data['firstname'] ?? '');
$lastname = trim($data['lastname'] ?? '');
$username = trim($data['username'] ?? '');
$email = trim($data['email'] ?? '');
$phone = trim($data['phone'] ?? '');
$password = $data['password'] ?? '';
$password_confirm = $data['password_confirm'] ?? '';

// Liste des champs obligatoires avec messages personnalisés
$requiredFields = [
    'firstname' => 'Le prénom est obligatoire',
    'lastname' => 'Le nom est obligatoire',
    'username' => 'Le nom d\'utilisateur est obligatoire',
    'email' => 'L\'email est obligatoire',
    'phone' => 'Le téléphone est obligatoire',
    'password' => 'Le mot de passe est obligatoire',
    'password_confirm' => 'La confirmation du mot de passe est obligatoire'
];

// Vérification des champs requis
foreach ($requiredFields as $field => $message) {
    if (empty($$field)) {
        http_response_code(400);
        echo json_encode(['error' => $message]);
        exit;
    }
}

// Validation de l'email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Format d\'email invalide']);
    exit;
}

// Validation du téléphone (format sénégalais amélioré)
if (!preg_match('/^(\+221|221)?[7][0-9]{8}$/', $phone)) {
    http_response_code(400);
    echo json_encode(['error' => 'Numéro de téléphone invalide. Format attendu : +221781234567 ou 781234567']);
    exit;
}

// Normaliser le numéro de téléphone
if (!str_starts_with($phone, '+221') && !str_starts_with($phone, '221')) {
    $phone = '+221' . $phone;
}

// Vérification du mot de passe
if ($password !== $password_confirm) {
    http_response_code(400);
    echo json_encode(['error' => 'Les mots de passe ne correspondent pas']);
    exit;
}

// Vérification de la force du mot de passe (améliorée)
if (strlen($password) < 8) {
    http_response_code(400);
    echo json_encode(['error' => 'Le mot de passe doit contenir au moins 8 caractères']);
    exit;
}

// Validation plus stricte du mot de passe
if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $password)) {
    http_response_code(400);
    echo json_encode(['error' => 'Le mot de passe doit contenir au moins une minuscule, une majuscule et un chiffre']);
    exit;
}

// Hachage sécurisé du mot de passe
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// Vérifier les doublons avant insertion
try {
    // Vérifier email existant
    $checkEmail = $pdo->prepare("SELECT id FROM users WHERE email = :email");
    $checkEmail->execute([':email' => $email]);
    if ($checkEmail->fetch()) {
        http_response_code(400);
        echo json_encode(['error' => 'Email déjà utilisé']);
        exit;
    }

    // Vérifier username existant
    $checkUsername = $pdo->prepare("SELECT id FROM users WHERE username = :username");
    $checkUsername->execute([':username' => $username]);
    if ($checkUsername->fetch()) {
        http_response_code(400);
        echo json_encode(['error' => 'Nom d\'utilisateur déjà utilisé']);
        exit;
    }

    // Insertion de l'utilisateur
    $query = "INSERT INTO users (firstname, lastname, username, email, phone, password_hash, created_at)
              VALUES (:firstname, :lastname, :username, :email, :phone, :password_hash, NOW())";
    
    $stmt = $pdo->prepare($query);
    $result = $stmt->execute([
        ':firstname' => $firstname,
        ':lastname' => $lastname,
        ':username' => $username,
        ':email' => $email,
        ':phone' => $phone,
        ':password_hash' => $password_hash
    ]);

    if ($result) {
        $userId = $pdo->lastInsertId();
        
        // Succès avec plus d'informations
        http_response_code(201); // Created
        echo json_encode([
            'success' => 'Inscription réussie ✅',
            'message' => 'Votre compte a été créé avec succès',
            'user' => [
                'id' => $userId,
                'firstname' => $firstname,
                'lastname' => $lastname,
                'username' => $username,
                'email' => $email,
                'phone' => $phone
            ]
        ]);
    } else {
        throw new Exception('Erreur lors de l\'insertion');
    }

} catch (PDOException $e) {
    // Log de l'erreur pour le développement
    error_log("Erreur base de données: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'error' => 'Erreur serveur lors de l\'inscription',
        'debug' => $e->getMessage() // À retirer en production
    ]);
} catch (Exception $e) {
    error_log("Erreur générale: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'error' => 'Erreur interne du serveur'
    ]);
}
?>