<?php
session_start();
include '../../config/db.php'; // Koneksi ke database

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'register') {
    $fullname = filter_input(INPUT_POST, 'fullname', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = htmlspecialchars(trim($_POST['role']));

    // Validasi data
    if (empty($fullname) || empty($email) || empty($phone) || empty($password) || empty($confirm_password) || empty($role)) {
        header("Location: ../auth/register.php?error=empty_fields");
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: ../auth/register.php?error=invalid_email");
        exit();
    }

    if (strlen($password) < 8) {
        header("Location: ../auth/register.php?error=weak_password");
        exit();
    }

    if ($password !== $confirm_password) {
        header("Location: ../auth/register.php?error=password_mismatch");
        exit();
    }

    if ($role !== 'admin' && $role !== 'pengurus') {
        header("Location: ../auth/register.php?error=invalid_role");
        exit();
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    try {
        // Cek apakah email sudah ada
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            header("Location: ../auth/register.php?error=email_taken");
            exit();
        }

        // Insert user ke database
        $stmt = $pdo->prepare("INSERT INTO users (fullname, email, phone, password, role) VALUES (:fullname, :email, :phone, :password, :role)");
        $stmt->bindParam(':fullname', $fullname);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':role', $role);

        if ($stmt->execute()) {
            header("Location: ../auth/login.php?success=registered");
        } else {
            error_log("Database Error: " . implode(", ", $stmt->errorInfo()));
            header("Location: ../auth/register.php?error=registration_failed");
        }
    } catch (PDOException $e) {
        error_log("PDO Error: " . $e->getMessage());
        header("Location: ../auth/register.php?error=server_error");
    }
    exit();
}
?>
