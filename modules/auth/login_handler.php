<?php
session_start();
include '../../config/db.php'; // Koneksi ke database

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'login') {
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        header("Location: ../auth/login.php?error=empty_fields");
        exit();
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->bindParam(':email', $username);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];

                // Catat aktivitas login
                $stmt = $pdo->prepare(
                    "INSERT INTO activity_log (user_id, activity, activity_description, event_type) 
                    VALUES (:user_id, 'Login', 'Pengguna login ke sistem', 'login')"
                );
                $stmt->bindParam(':user_id', $user['id']);
                $stmt->execute();

                if ($user['role'] == 'admin') {
                    header("Location: ../../modules/dashboard/admin_dashboard.php");
                } elseif ($user['role'] == 'pengurus') {
                    header("Location: ../../modules/pengurus_dashboard/dashboard_pengurus.php");
                } else {
                    header("Location: ../auth/login.php?error=unknown_role");
                }
            } else {
                header("Location: ../auth/login.php?error=invalid_password");
            }
        } else {
            header("Location: ../auth/login.php?error=user_not_found");
        }
    } catch (PDOException $e) {
        error_log("PDO Error: " . $e->getMessage());
        header("Location: ../auth/login.php?error=server_error");
    }
    exit();
}
?>
