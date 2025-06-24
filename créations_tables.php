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

    // ✅ Création de la table users
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id SERIAL PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            email VARCHAR(100) NOT NULL UNIQUE,
            password TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
    ");

    // ✅ Création de la table messages
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS messages (
            id SERIAL PRIMARY KEY,
            user_id INTEGER NOT NULL,
            content TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        );
    ");

    echo json_encode(['success' => 'Tables users et messages créées avec succès ✅']);
} catch (PDOException $e) {
    die(json_encode(['error' => 'Erreur PDO : ' . $e->getMessage()]));
}
?>
