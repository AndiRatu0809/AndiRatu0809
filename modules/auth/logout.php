<?php
session_start();
include '../../config/db.php';

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // Catat aktivitas logout
    $stmt = $pdo->prepare("
        INSERT INTO activity_log (user_id, activity, activity_description, event_type) 
        VALUES (:user_id, 'Logout', 'Pengguna logout dari sistem', 'logout')
    ");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
}

// Hapus sesi
session_destroy();
header("Location: ../auth/login.php?success=logged_out");
exit();
?>
