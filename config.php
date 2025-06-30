<?php
$databaseUrl = getenv("DATABASE_URL");

if (!$databaseUrl) {
    die(json_encode(["error" => "DATABASE_URL non définie"]));
}

$dbopts = parse_url($databaseUrl);

$host = $dbopts["host"];
$port = $dbopts["port"] ?? "5432"; // Valeur par défaut si non fournie
$user = $dbopts["user"];
$pass = $dbopts["pass"];
$dbname = ltrim($dbopts["path"], "/");

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    $db = new PDO($dsn, $user, $pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die(json_encode(["error" => $e->getMessage()]));
}
