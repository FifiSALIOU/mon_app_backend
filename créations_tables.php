<?php
// 🔐 Récupérer l'URL de la base depuis l'environnement
$databaseUrl = getenv("DATABASE_URL");

if (!$databaseUrl) {
    die(json_encode(['error' => 'DATABASE_URL non définie']));
}

// 🧩 Extraire les infos depuis l'URL
$db = parse_url($databaseUrl);

$host = $db['host'];
$port = $db['port'] ?? '5432';
$user = $db['user'];
$pass = $db['pass'];
$dbname = ltrim($db['path'], '/');

// 🔗 Connexion à PostgreSQL avec PDO
try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 🔄 Ajouter les colonnes manquantes à la table users
    $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS firstname VARCHAR(100)");
    $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS lastname VARCHAR(100)");
    $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS phone VARCHAR(20)");
    
    // 🔍 Vérifier si la colonne password existe avant de la renommer
    $checkPasswordColumn = $pdo->query("
        SELECT column_name 
        FROM information_schema.columns 
        WHERE table_name = 'users' AND column_name = 'password'
    ");

    if ($checkPasswordColumn->rowCount() > 0) {
        // 🔄 Renommer seulement si password existe
        $pdo->exec("ALTER TABLE users RENAME COLUMN password TO password_hash");
        $pdo->exec("ALTER TABLE users ALTER COLUMN password_hash TYPE VARCHAR(255)");
    } else {
        // 🔄 Créer password_hash si elle n'existe pas
        $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS password_hash VARCHAR(255)");
    }

    echo json_encode(['success' => 'Table users mise à jour avec succès ✅']);
    
} catch (PDOException $e) {
    die(json_encode(['error' => 'Erreur PDO : ' . $e->getMessage()]));
}
?>