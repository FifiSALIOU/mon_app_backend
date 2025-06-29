<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: connexion.php');
    exit;
}
require 'db.php';

$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT name FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

echo "Bienvenue, " . htmlspecialchars($user['name']) . "!<br>";
echo "<a href='profil.php'>Modifier mon profil</a> | ";
echo "<a href='messages.php'>Messages</a> | ";
echo "<a href='logout.php'>Déconnexion</a>";
?>
