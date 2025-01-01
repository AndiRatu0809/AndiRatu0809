<?php
// Koneksi PDO ke database
try {
    $pdo = new PDO("mysql:host=localhost;dbname=hipermawa_db", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}
?>
