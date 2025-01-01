<?php
session_start();
include '../../config/db.php';

// Mengecek apakah pengguna sudah login dan memiliki peran admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {
    $activity_id = $_GET['id'];

    // Menghapus kegiatan berdasarkan ID
    $stmt = $pdo->prepare("DELETE FROM activities WHERE id = ?");
    $stmt->execute([$activity_id]);

    header("Location: view_activity.php"); // Kembali ke halaman kegiatan setelah dihapus
    exit();
} else {
    echo "Kegiatan tidak ditemukan.";
    exit();
}
?>
