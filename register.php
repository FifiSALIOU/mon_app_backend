<?php
require 'config.php';
$data = json_decode(file_get_contents("php://input"), true);

$prenom = $data["firstname"];
$nom = $data["lastname"];
$email = $data["email"];
$phone = $data["phone"];
$password = password_hash($data["password"], PASSWORD_DEFAULT);

$sql = "INSERT INTO users (firstname, lastname, email, phone, password_hash) VALUES (?, ?, ?, ?, ?)";
$stmt = $pdo->prepare($sql);
$stmt->execute([$prenom, $nom, $email, $phone, $password]);

echo json_encode(["message" => "Inscription réussie"]);
?>
