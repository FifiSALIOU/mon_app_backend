<?php
header('Content-Type: application/json'); // La réponse sera toujours du JSON
require 'db.php'; // Votre fichier de connexion à la base de données

// Débogage : Affiche les logs dans le tableau de bord Render.com
error_log("Requête reçue sur register.php à " . date('Y-m-d H:i:s'));
error_log("Méthode: " . $_SERVER['REQUEST_METHOD']);
error_log("Content-Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'N/A'));
error_log("Données POST reçues: " . json_encode($_POST)); // Log les données $_POST

// Le script s'attend à une méthode POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Les données sont maintenant directement dans $_POST car l'app Android envoie un FormBody
    $firstname = trim($_POST['firstname'] ?? '');
    $lastname = trim($_POST['lastname'] ?? '');
    $username = trim($_POST['username'] ?? ''); // Récupère le username
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
} else {
    // Si la méthode n'est pas POST, renvoie une erreur 405
    http_response_code(405);
    echo json_encode(['error' => 'Méthode non autorisée']);
    exit;
}

// --- Validation des champs côté serveur ---
// (Les validations sont cruciales et le PHP est censé renvoyer des erreurs si elles échouent)
$requiredFields = [
    'firstname' => 'Le prénom est obligatoire', 'lastname' => 'Le nom est obligatoire',
    'username' => 'Le nom d\'utilisateur est obligatoire', 'email' => 'L\'email est obligatoire',
    'phone' => 'Le téléphone est obligatoire', 'password' => 'Le mot de passe est obligatoire',
    'password_confirm' => 'La confirmation du mot de passe est obligatoire'
];

foreach ($requiredFields as $field => $message) {
    if (empty($$field)) {
        http_response_code(400); echo json_encode(['error' => $message]); exit;
    }
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400); echo json_encode(['error' => 'Format d\'email invalide']); exit;
}
if (!preg_match('/^\+?[0-9]{9,15}$/', $phone)) {
    http_response_code(400); echo json_encode(['error' => 'Numéro de téléphone invalide. Format attendu : +221781234567']); exit;
}
if ($password !== $password_confirm) {
    http_response_code(400); echo json_encode(['error' => 'Les mots de passe ne correspondent pas']); exit;
}
if (strlen($password) < 8) {
    http_response_code(400); echo json_encode(['error' => 'Le mot de passe doit contenir au moins 8 caractères']); exit;
}

// Hachage sécurisé du mot de passe
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// --- Insertion dans la base de données ---
$query = "INSERT INTO users (firstname, lastname, username, email, phone, password_hash, created_at)
          VALUES (:firstname, :lastname, :username, :email, :phone, :password_hash, NOW())";

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        ':firstname' => $firstname, ':lastname' => $lastname, ':username' => $username,
        ':email' => $email, ':phone' => $phone, ':password_hash' => $password_hash
    ]);

    // Réponse de succès si tout s'est bien passé
    echo json_encode(['success' => 'Inscription réussie ✅', 'user' => [
        'firstname' => $firstname, 'lastname' => $lastname, 'email' => $email
    ]]);
} catch (PDOException $e) {
    // Gestion des erreurs de base de données (ex: email/username déjà utilisés)
    if ($e->getCode() === '23505') { // Code d'erreur pour violation de contrainte unique en PostgreSQL
        if (strpos($e->getMessage(), 'users_email_key') !== false) {
            $error = 'Email déjà utilisé';
        } elseif (strpos($e->getMessage(), 'users_username_key') !== false) {
            $error = 'Nom d\'utilisateur déjà utilisé';
        } else {
            $error = 'Donnée en double (contrainte unique violée)';
        }
    } else {
        $error = 'Erreur lors de l\'inscription';
    }
    http_response_code(400); // Bad Request
    echo json_encode(['error' => $error, 'debug' => $e->getMessage()]);
}
?>