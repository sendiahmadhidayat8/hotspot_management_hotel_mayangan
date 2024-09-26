<?php
$host = ''; // Ganti dengan host database kamu
$db = ''; // Ganti dengan nama database kamu
$user = ''; // Ganti dengan username database kamu
$pass = ''; // Ganti dengan password database kamu

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
