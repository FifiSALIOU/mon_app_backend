<?php
include 'config.php';
$db->exec("
CREATE TABLE IF NOT EXISTS users (
  id SERIAL PRIMARY KEY,
  prenom VARCHAR(100),
  nom VARCHAR(100),
  email VARCHAR(150) UNIQUE,
  password VARCHAR(255)
);
CREATE TABLE IF NOT EXISTS messages (
  id SERIAL PRIMARY KEY,
  user_id INT REFERENCES users(id),
  content TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);");
echo "Tables créées.";
