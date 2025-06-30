<?php
// Récupérer l'URL de connexion PostgreSQL de Render
$databaseUrl = getenv("DATABASE_URL");
if (!$databaseUrl) {
    echo json_encode(["error" => "DATABASE_URL non définie"]);
    exit;
}

$db = parse_url($databaseUrl);

$host = $db["host"];
$port = $db["port"] ?? 5432; // Défaut à 5432 si manquant
$user = $db["user"];
$pass = $db["pass"];
$dbname = ltrim($db["path"], "/");

try {
    $db = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(["error" => "Connexion à la base échouée : " . $e->getMessage()]);
    exit;
}
?>
