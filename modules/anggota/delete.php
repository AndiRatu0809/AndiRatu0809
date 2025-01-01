<?php
// modules/anggota/delete.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

require_once '../../config/database.php';

if (isset($_GET['id'])) {
    $database = new Database();
    $db = $database->getConnection();
    
    try {
        // Cek apakah data masih digunakan di tabel lain
        $query = "DELETE FROM anggota WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(":id", $_GET['id']);
        $stmt->execute();
        
        $_SESSION['success'] = "Data anggota berhasil dihapus.";
    } catch(PDOException $e) {
        $_SESSION['error'] = "Gagal menghapus data anggota: " . $e->getMessage();
    }
}

header("Location: index.php");
exit();
?>