<?php
session_start();
include '../../config/db.php';

// Pastikan pengguna sudah login dan perannya adalah pengurus
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pengurus') {
    header("Location: login.php");
    exit();
}

// Tangani form pengunggahan
if (isset($_POST['submit_activity'])) {
    $activity_name = $_POST['activity_name'];
    $activity_description = $_POST['activity_description'];
    $user_id = $_SESSION['user_id'];

    // Tangani pengunggahan file
    $file = $_FILES['activity_file'];
    $file_name = $file['name'];
    $file_tmp = $file['tmp_name'];
    $file_size = $file['size'];
    $file_error = $file['error'];

    // Periksa apakah file berhasil diunggah
    if ($file_error === 0) {
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // Validasi jenis file
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'mp4', 'avi', 'pdf'];
        if (in_array($file_ext, $allowed_extensions)) {
            // Tentukan nama file baru untuk menghindari duplikasi
            $new_file_name = uniqid('', true) . '.' . $file_ext;
            $file_destination = '../../uploads/' . $new_file_name;

            // Pindahkan file ke folder uploads
            if (move_uploaded_file($file_tmp, $file_destination)) {
                // Simpan data kegiatan ke database
                $stmt = $pdo->prepare("INSERT INTO activities (user_id, activity_name, activity_description, file_path) VALUES (?, ?, ?, ?)");
                $stmt->execute([$user_id, $activity_name, $activity_description, $new_file_name]);

                header("Location: pengurus_dashboard.php?success=activity_uploaded");
                exit();
            } else {
                echo "Gagal mengunggah file.";
            }
        } else {
            echo "File yang diunggah tidak didukung. Harap unggah file dengan format gambar, video, atau PDF.";
        }
    } else {
        echo "Terjadi kesalahan saat mengunggah file.";
    }
}
?>
