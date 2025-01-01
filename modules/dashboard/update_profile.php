<?php
session_start();
include '../../config/db.php';

// Periksa apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Validasi input
$fullname = trim($_POST['fullname']);
$email = trim($_POST['email']);
$password = trim($_POST['password']);
$profile_picture = $_FILES['profile_picture'];

// Update foto profil jika diunggah
if (!empty($profile_picture['name'])) {
    $target_dir = "../../uploads/";
    $target_file = $target_dir . basename($profile_picture['name']);
    move_uploaded_file($profile_picture['tmp_name'], $target_file);

    // Simpan path gambar ke database
    $stmt = $pdo->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
    $stmt->execute([$profile_picture['name'], $user_id]);
}

// Update nama lengkap dan email
$stmt = $pdo->prepare("UPDATE users SET fullname = ?, email = ? WHERE id = ?");
$stmt->execute([$fullname, $email, $user_id]);

// Update password jika diisi
if (!empty($password)) {
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->execute([$hashed_password, $user_id]);
}

header("Location: profile.php?success=updated");
exit();
?>
