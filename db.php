<?php
// Récupérer l'URL de la base depuis la variable d'environnement
$databaseUrl = getenv("DATABASE_URL");

if (!$databaseUrl) {
    die(json_encode(['error' => 'DATABASE_URL non définie dans l\'environnement']));
}

// Analyser l'URL PostgreSQL
$db = parse_url($databaseUrl);

$host = $db['host'];
$port = '5432';
$user = $db['user'];
$pass = $db['pass'];
$dbname = ltrim($db['path'], '/'); // enlever le / devant le nom

// Connexion PDO
try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "Connexion réussie ✅";
} catch (PDOException $e) {
    die(json_encode(['error' => 'Erreur DB : ' . $e->getMessage()]));
}
?>
