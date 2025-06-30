<?php
// config.php
try {
    $db = new PDO("pgsql:host=host_name;port=5432;dbname=mon_app_backend", "postgres", "password");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['error' => 'Connexion à la base échouée : ' . $e->getMessage()]);
    exit;
}
