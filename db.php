<?php
// db.php
$databaseUrl = getenv("DATABASE_URL");
if (!$databaseUrl) {
    error_log('Erreur critique: DATABASE_URL non définie');
    header('Content-Type: application/json');
    die(json_encode(['error' => 'Configuration de base de données manquante']));
}
$db = parse_url($databaseUrl);
$host = $db['host'];
$port = $db['port'] ?? '5432';
$user = $db['user'];
$pass = $db['pass'];
$dbname = ltrim($db['path'], '/');

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    error_log('Erreur DB: ' . $e->getMessage());
    header('Content-Type: application/json');
    die(json_encode(['error' => 'Erreur de connexion à la base de données']));
}
