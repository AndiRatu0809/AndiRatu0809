<?php
session_start();
include '../../config/db.php';

// Pastikan pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Direktori penyimpanan file
$uploadDir = '../../uploads/documents/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Cek apakah form di-submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil file dan informasi deskripsi
    $file = $_FILES['file'];
    $description = $_POST['description'];
    $userId = $_SESSION['user_id'];

    // Validasi file
    $allowedTypes = ['image/jpeg', 'image/png', 'video/mp4', 'application/pdf'];
    $fileType = mime_content_type($file['tmp_name']);
    $fileSize = $file['size'];
    $maxSize = 10 * 1024 * 1024; // 10MB

    if (!in_array($fileType, $allowedTypes)) {
        echo "Tipe file tidak didukung.";
        exit();
    }

    if ($fileSize > $maxSize) {
        echo "Ukuran file terlalu besar. Maksimal 10MB.";
        exit();
    }

    // Pindahkan file ke direktori upload
    $fileName = time() . '_' . basename($file['name']);
    $filePath = $uploadDir . $fileName;
    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        // Simpan metadata ke database
        $stmt = $pdo->prepare("
            INSERT INTO documents (name, description, file_path, file_type, is_verified, uploaded_by)
            VALUES (:name, :description, :file_path, :file_type, 0, :uploaded_by)
        ");
        $stmt->execute([
            ':name' => $file['name'],
            ':description' => $description,
            ':file_path' => $fileName,
            ':file_type' => $fileType,
            ':uploaded_by' => $userId
        ]);

        echo "File berhasil diupload.";
        header("Location: view_documents.php"); // Ganti sesuai dengan nama file halaman Anda
        exit();
    } else {
        echo "Terjadi kesalahan saat mengupload file.";
    }
}
?>
