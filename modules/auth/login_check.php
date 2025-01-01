<?php
session_start();
include '../../config/db.php'; // Pastikan file koneksi database sudah benar

// Cek apakah pengguna sudah login
if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    $user_id = $_SESSION['user_id'];
    $role = $_SESSION['role'];

    // Cek apakah role pengguna valid
    if ($role == 'admin') {
        // Arahkan ke halaman dashboard admin
        header("Location: ../../modules/dashboard/admin_dashboard.php");
        exit();
    } elseif ($role == 'pengurus') {
        // Arahkan ke halaman dashboard pengurus
        header("Location: ../../modules/dashboard/pengurus_dashboard.php");
        exit();
    } else {
        // Jika role tidak valid, logout dan arahkan ke login
        session_unset();
        session_destroy();
        header("Location: ../auth/login.php?error=unknown_role");
        exit();
    }
} else {
    // Jika pengguna belum login, arahkan ke halaman login
    header("Location: ../auth/login.php?error=not_logged_in");
    exit();
}
?>
