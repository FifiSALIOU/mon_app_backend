<?php 
header('Content-Type: application/json');
require 'db.php'; // Fichier de connexion à la base de données

// Gestion multi-format des données (JSON ou form-urlencoded)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    
    if (strpos($contentType, 'application/json') !== false) {
        $data = json_decode(file_get_contents('php://input'), true);
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

// Validation du téléphone (format international simplifié)
if (!preg_match('/^\+?[0-9]{9,15}$/', $phone)) {
    http_response_code(400);
    echo json_encode(['error' => 'Numéro de téléphone invalide. Format attendu : +221781234567']);
    exit;
}

// Vérification du mot de passe
if ($password !== $password_confirm) {
    http_response_code(400);
    echo json_encode(['error' => 'Les mots de passe ne correspondent pas']);
    exit;
}

// Vérification de la force du mot de passe
if (strlen($password) < 8) {
    http_response_code(400);
    echo json_encode(['error' => 'Le mot de passe doit contenir au moins 8 caractères']);
    exit;
}

// Hachage sécurisé du mot de passe
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// Requête d'insertion PostgreSQL
$query = "INSERT INTO users (firstname, lastname, username, email, phone, password_hash, created_at)
          VALUES (:firstname, :lastname, :username, :email, :phone, :password_hash, NOW())";

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        ':firstname' => $firstname,
        ':lastname' => $lastname,
        ':username' => $username,
        ':email' => $email,
        ':phone' => $phone,
        ':password_hash' => $password_hash
    ]);
    
    // Succès
    echo json_encode(['success' => 'Inscription réussie ✅', 'user' => [
        'firstname' => $firstname,
        'lastname' => $lastname,
        'email' => $email
    ]]);
} catch (PDOException $e) {
    // Gestion spécifique des erreurs de contrainte unique
    if ($e->getCode() === '23505') {
        if (strpos($e->getMessage(), 'users_email_key') !== false) {
            $error = 'Email déjà utilisé';
        } else {
            $error = 'Donnée en double (contrainte unique violée)';
        }
    } else {
        $error = 'Erreur lors de l\'inscription';
    }
    
    http_response_code(400);
    echo json_encode(['error' => $error, 'debug' => $e->getMessage()]);
}
?>