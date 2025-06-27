<?php
// db.php - Fichier de connexion à PostgreSQL

// Désactiver l'affichage des erreurs en production
// error_reporting(0);

$databaseUrl = getenv("DATABASE_URL");

if (!$databaseUrl) {
    // Journaliser l'erreur avant de mourir
    error_log('Erreur critique: DATABASE_URL non définie');
    header('Content-Type: application/json');
    die(json_encode(['error' => 'Configuration de base de données manquante']));
}

// Analyser l'URL PostgreSQL
$db = parse_url($databaseUrl);

if (!$db || !isset($db['host'], $db['user'], $db['pass'], $db['path'])) {
    error_log('Erreur de configuration DB: URL invalide - ' . $databaseUrl);
    header('Content-Type: application/json');
    die(json_encode(['error' => 'Configuration de base de données invalide']));
}

$host = $db['host'];
$port = $db['port'] ?? '5432'; // Port par défaut PostgreSQL
$user = $db['user'];
$pass = $db['pass'];
$dbname = ltrim($db['path'], '/');

// Connexion PDO avec gestion d'erreurs améliorée
try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false, // Désactive l'émulation pour la sécurité
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_PERSISTENT => false // Préférez les connexions non persistantes
    ];
    
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Tester la connexion
    $pdo->query("SELECT 1");
    
} catch (PDOException $e) {
    // Journalisation détaillée
    error_log('Erreur DB CONNEXION: ' . $e->getMessage());
    error_log('DSN: ' . $dsn);
    
    // Réponse utilisateur sans détails sensibles
    header('Content-Type: application/json');
    die(json_encode([
        'error' => 'Erreur de base de données',
        'debug' => (getenv('APP_ENV') === 'development') ? $e->getMessage() : null
    ]));
}
?>