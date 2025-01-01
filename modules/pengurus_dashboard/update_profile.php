<?php
session_start();
include dirname(__DIR__, 2) . '/config/db.php';

// Pastikan pengguna telah login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pengurus') {
    header("Location: ../../dashboard_pengurus/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $new_password = $_POST['new_password'];
    $old_password = $_POST['old_password'];

    try {
        // Ambil data pengguna
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verifikasi password lama
        if (!password_verify($old_password, $user['password'])) {
            $_SESSION['error'] = "Password lama salah!";
            header("Location: profile.php");
            exit();
        }

        // Proses upload foto profil
        $profile_photo = $user['profile_photo']; // Gunakan foto lama sebagai default
        if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] == 0) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $max_file_size = 5 * 1024 * 1024; // 5 MB

            // Validasi tipe dan ukuran file
            if (in_array($_FILES['profile_photo']['type'], $allowed_types) && 
                $_FILES['profile_photo']['size'] <= $max_file_size) {
                
                // Buat direktori upload jika belum ada
                $upload_dir = dirname(__DIR__, 2) . '/uploads/profile_photos/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                // Hapus foto lama jika ada
                if ($profile_photo && file_exists($upload_dir . $profile_photo)) {
                    unlink($upload_dir . $profile_photo);
                }

                // Generate nama file unik
                $file_extension = pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION);
                $new_filename = uniqid('profile_') . '.' . $file_extension;
                $destination = $upload_dir . $new_filename;

                // Pindahkan file yang diupload
                if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $destination)) {
                    $profile_photo = $new_filename;
                } else {
                    throw new Exception("Gagal mengunggah foto profil");
                }
            } else {
                $_SESSION['error'] = "Tipe atau ukuran file tidak valid. Maksimal 5 MB, hanya JPEG, PNG, dan GIF.";
                header("Location: profile.php");
                exit();
            }
        }

        // Query update
        if (!empty($new_password)) {
            // Update email, password, dan foto profil
            $stmt = $pdo->prepare("UPDATE users SET email = ?, password = ?, profile_photo = ? WHERE id = ?");
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt->execute([$email, $hashed_password, $profile_photo, $_SESSION['user_id']]);
        } else {
            // Update email dan foto profil
            $stmt = $pdo->prepare("UPDATE users SET email = ?, profile_photo = ? WHERE id = ?");
            $stmt->execute([$email, $profile_photo, $_SESSION['user_id']]);
        }

        // Jika berhasil, arahkan ke halaman profil
        $_SESSION['success'] = "Profil berhasil diperbarui!";
        header("Location: profile.php");
        exit();

    } catch (Exception $e) {
        // Jika terjadi error, arahkan ke halaman dengan pesan error
        $_SESSION['error'] = "Error: " . $e->getMessage();
        header("Location: profile.php");
        exit();
    }
}
?>