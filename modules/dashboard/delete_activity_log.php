<?php
session_start();
include '../../config/db.php';

if (isset($_GET['id'])) {
    $log_id = $_GET['id'];

    // Hapus log aktivitas
    $stmt = $pdo->prepare("DELETE FROM activity_log WHERE id = ?");
    $stmt->execute([$log_id]);

    // Kembali ke halaman log aktivitas
    header("Location: view_activity_log.php");
}
?>
