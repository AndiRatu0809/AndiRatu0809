<?php
session_start();

// Cek apakah pengguna sudah login dan memiliki role admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Koneksi database
require_once '../../config/database.php';
$database = new Database();
$db = $database->getConnection();

if (isset($_POST['add_kegiatan'])) {
    // Ambil data dari form
    $nama_kegiatan = $_POST['nama_kegiatan'] ?? null;
    $tanggal_mulai = $_POST['tanggal_mulai'] ?? null;
    $tanggal_selesai = $_POST['tanggal_selesai'] ?? null;
    $status = $_POST['status'] ?? null;
    $cabang_id = $_POST['cabang_id'] ?? null;

    // Validasi form (kecuali dokumentasi yang opsional)
    if (empty($nama_kegiatan) || empty($tanggal_mulai) || empty($tanggal_selesai) || empty($status) || empty($cabang_id)) {
        echo "<script>alert('Semua field wajib diisi, kecuali dokumentasi!');</script>";
        exit();
    }

    // Inisialisasi variabel dokumentasi
    $dokumentasi = null;

    // Proses unggah file dokumentasi (jika ada)
    if (isset($_FILES['dokumentasi']) && $_FILES['dokumentasi']['error'] === UPLOAD_ERR_OK) {
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'mp4', 'avi', 'mov'];
        $max_file_size = 5 * 1024 * 1024; // Maksimal 5 MB
        $file_extension = strtolower(pathinfo($_FILES['dokumentasi']['name'], PATHINFO_EXTENSION));
        $upload_dir = '../../uploads/';
        $file_name = time() . '_' . preg_replace('/\s+/', '_', $_FILES['dokumentasi']['name']);
        $upload_path = $upload_dir . $file_name;

        // Validasi ukuran file
        if ($_FILES['dokumentasi']['size'] > $max_file_size) {
            echo "<script>alert('Ukuran file terlalu besar. Maksimal 5MB.');</script>";
            exit();
        }

        // Validasi format file
        if (!in_array($file_extension, $allowed_extensions)) {
            echo "<script>alert('Format file tidak valid. Hanya gambar dan video yang diizinkan.');</script>";
            exit();
        }

        // Periksa apakah folder upload ada
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true); // Buat folder jika belum ada
        }

        // Pindahkan file ke folder upload
        if (move_uploaded_file($_FILES['dokumentasi']['tmp_name'], $upload_path)) {
            $dokumentasi = $file_name; // Simpan nama file untuk database
        } else {
            echo "<script>alert('Gagal mengunggah file. Pastikan folder memiliki izin tulis.');</script>";
            exit();
        }
    } elseif ($_FILES['dokumentasi']['error'] !== UPLOAD_ERR_NO_FILE) {
        echo "<script>alert('Terjadi kesalahan saat mengunggah file.');</script>";
        exit();
    }

    // Query untuk menambahkan kegiatan ke database
    $query = "INSERT INTO kegiatan (nama_kegiatan, tanggal_mulai, tanggal_selesai, status, cabang_id, dokumentasi) 
              VALUES (:nama_kegiatan, :tanggal_mulai, :tanggal_selesai, :status, :cabang_id, :dokumentasi)";
    $stmt = $db->prepare($query);

    $stmt->bindParam(':nama_kegiatan', $nama_kegiatan);
    $stmt->bindParam(':tanggal_mulai', $tanggal_mulai);
    $stmt->bindParam(':tanggal_selesai', $tanggal_selesai);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':cabang_id', $cabang_id);
    $stmt->bindParam(':dokumentasi', $dokumentasi);

    // if ($stmt->execute()) {
    //     echo "<script>alert('Kegiatan berhasil ditambahkan!');</script>";
    //     echo "<script>window.location.href = 'dashboard.php';</script>";
    // } else {
    //     echo "<script>alert('Gagal menambahkan kegiatan.');</script>";
    // }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Kegiatan</title>
    <link rel="stylesheet" href="../../assets/css/bootstrap.min.css">
    <style>
       body {
    font-family: 'Arial', sans-serif;
    background-color: #000; /* Latar belakang hitam */
    margin: 0;
    padding: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
}

 /* Logo Styles */
 .login-logo {
        display: block;
        margin: 0 auto 20px;
        max-width: 150px; 
        height: 150px; /* Menetapkan tinggi dan lebar yang sama agar logo menjadi bundar */
        border-radius: 50%; /* Menjadikan gambar berbentuk bundar */
        object-fit: cover; /* Menjaga gambar tetap terpusat dan sesuai ukuran */
    }

.container {
    background-color: #222; /* Warna latar belakang form */
    color: #fff; /* Teks berwarna putih */
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
    max-width: 600px;
    width: 100%;
}

h1 {
    text-align: center;
    margin-bottom: 20px;
    font-size: 28px;
}

.form-group {
    margin-bottom: 20px;
}

label {
    font-size: 16px;
    color: #ddd;
    font-weight: bold;
}

input[type="text"], input[type="date"], select, input[type="file"] {
    width: 100%;
    padding: 10px;
    margin-top: 5px;
    border: 1px solid #444;
    border-radius: 8px;
    background-color: #333;
    color: #fff;
    font-size: 16px;
}

input[type="text"]:focus, input[type="date"]:focus, select:focus, input[type="file"]:focus {
    border-color: #57a1f8;
    outline: none;
}

button[type="submit"] {
    width: 100%;
    padding: 12px;
    background-color: #57a1f8;
    border: none;
    border-radius: 8px;
    color: #fff;
    font-size: 16px;
    cursor: pointer;
    transition: background-color 0.3s;
}

button[type="submit"]:hover {
    background-color: #3a85d7;
}

.btn {
    margin-top: 15px;
}

input[type="file"] {
    font-size: 14px;
    padding: 5px;
}

input[type="file"]:hover {
    background-color: #444;
}

footer {
    margin-top: 30px;
    text-align: center;
    font-size: 14px;
    color: #aaa;
}

footer a {
    color: #57a1f8;
    text-decoration: none;
}

footer a:hover {
    text-decoration: underline;
}

    </style>
</head>
<body>
    <div class="container">
    <img src="../../assets/img/logo.png" alt="Logo" class="login-logo">
        <h1>Tambah Kegiatan</h1>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="nama_kegiatan">Nama Kegiatan</label>
                <input type="text" class="form-control" id="nama_kegiatan" name="nama_kegiatan" required>
            </div>
            <div class="form-group">
                <label for="tanggal_mulai">Tanggal Mulai</label>
                <input type="date" class="form-control" id="tanggal_mulai" name="tanggal_mulai" required>
            </div>
            <div class="form-group">
                <label for="tanggal_selesai">Tanggal Selesai</label>
                <input type="date" class="form-control" id="tanggal_selesai" name="tanggal_selesai" required>
            </div>
            <div class="form-group">
                <label for="status">Status</label>
                <select class="form-control" id="status" name="status" required>
                    <option value="Pending">Pending</option>
                    <option value="Sedang Berlangsung">Sedang Berlangsung</option>
                    <option value="Selesai">Selesai</option>
                </select>
            </div>
            <div class="form-group">
                <label for="cabang_id">Cabang ID</label>
                <input type="text" class="form-control" id="cabang_id" name="cabang_id" required>
            </div>
            <div class="form-group">
                <label for="dokumentasi">Dokumentasi (opsional, gambar/video)</label>
                <input type="file" class="form-control" id="dokumentasi" name="dokumentasi" accept="image/*,video/*">
            </div>
            <button type="submit" name="add_kegiatan" class="btn btn-primary">Tambah Kegiatan</button>
        </form>
    </div>
</body>
</html>
