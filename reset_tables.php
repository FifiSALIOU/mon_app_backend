<?php
require 'config.php';

try {
    // Supprimer les tables existantes si elles existent
    $db->exec("
        DROP TABLE IF EXISTS messages;
        DROP TABLE IF EXISTS users;
        
        CREATE TABLE users (
            id SERIAL PRIMARY KEY,
            prenom VARCHAR(100) NOT NULL,
            nom VARCHAR(100) NOT NULL,
            email VARCHAR(255) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL
        );
        
        CREATE TABLE messages (
            id SERIAL PRIMARY KEY,
            user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
            content TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
    ");
    echo json_encode(['success' => true, 'message' => 'Tables recréées avec succès']);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
