<?php
$databaseUrl = getenv("DATABASE_URL");

if (!$databaseUrl) {
    die(json_encode(["error" => "DATABASE_URL non définie"]));
}

$dbopts = parse_url($databaseUrl);
$host = $dbopts["host"];
$port = $dbopts["port"];
$user = $dbopts["user"];
$pass = $dbopts["pass"];
$dbname = ltrim($dbopts["path"], "/");

try {
    $db = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die(json_encode(["error" => $e->getMessage()]));
}
