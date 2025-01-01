<?php
session_start();
include dirname(__DIR__, 2) . '/config/db.php';

// Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    die("Akses ditolak! Anda harus login terlebih dahulu.");
}

// Cek apakah user valid di tabel users
try {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ? AND role = 'pengurus'");
    $stmt->execute([$_SESSION['user_id']]);
    if ($stmt->rowCount() === 0) {
        die("Error: Pengguna tidak terdaftar atau bukan pengurus.");
    }
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

// Cek apakah form telah di-submit untuk upload
if (isset($_POST['upload'])) {
    $nama = $_POST['nama'];
    $deskripsi = $_POST['deskripsi'];
    $lokasi_file = $_FILES['file']['name'];
    $uploaded_by = $_SESSION['user_id'];

    $target_dir = "../../uploads/"; // Pastikan direktori ini ada
    $target_file = $target_dir . basename($lokasi_file);

    // Upload file ke server
    if (move_uploaded_file($_FILES['file']['tmp_name'], $target_file)) {
        try {
            // Simpan informasi file ke database
            $stmt = $pdo->prepare("INSERT INTO dokumen (nama, deskripsi, lokasi_file, diupload_oleh) VALUES (?, ?, ?, ?)");
            $stmt->execute([$nama, $deskripsi, $target_file, $uploaded_by]);

            if ($stmt->rowCount() > 0) {
                echo "<div class='alert alert-success'>File berhasil diupload.</div>";
            } else {
                echo "<div class='alert alert-danger'>Gagal menyimpan data ke database.</div>";
            }
        } catch (PDOException $e) {
            die("Error: " . $e->getMessage());
        }
    } else {
        echo "<div class='alert alert-danger'>Gagal mengupload file ke server.</div>";
    }
}

// Hapus dokumen
if (isset($_POST['delete'])) {
    $dokumen_id = $_POST['dokumen_id'];
    
    // Ambil lokasi file dari database
    try {
        $stmt = $pdo->prepare("SELECT lokasi_file FROM dokumen WHERE id = ?");
        $stmt->execute([$dokumen_id]);
        $dokumen = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($dokumen) {
            // Hapus file dari server
            if (unlink($dokumen['lokasi_file'])) {
                // Hapus dari database
                $stmt = $pdo->prepare("DELETE FROM dokumen WHERE id = ?");
                $stmt->execute([$dokumen_id]);
                echo "<div class='alert alert-success'>Dokumen berhasil dihapus.</div>";
            } else {
                echo "<div class='alert alert-danger'>Gagal menghapus file dari server.</div>";
            }
        } else {
            echo "<div class='alert alert-danger'>Dokumen tidak ditemukan.</div>";
        }
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}

// Ambil dokumen terbaru
$dokumenTerbaru = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM dokumen ORDER BY created_at DESC LIMIT 10");
    $stmt->execute();
    $dokumenTerbaru = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Dokumen</title>
    <link rel="stylesheet" href="../styles.css">
    <style>
    /* General Reset */
    body {
        font-family: Arial, sans-serif;
        background-color: #f8f9fa;
        margin: 0;
        padding: 0;
    }

    /* Container Styling */
    .container {
        max-width: 800px;
        margin: 50px auto;
        background-color: #ffffff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    /* Table Styling */
    .table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    .table th,
    .table td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
    }

    .table th {
        background-color:rgb(0, 0, 0);
        color: #ffffff;
    }

    /* Form Elements */
    .form-group {
        margin-bottom: 15px;
    }

    .form-control {
        width: 100%;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 4px;
    }

    /* Buttons */
    .btn {
        display: inline-block;
        padding: 10px 15px;
        background-color:rgb(255, 0, 0);
        color: #ffffff;
        border: none;
        border-radius: 4px;
        text-align: center;
        text-decoration: none;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .btn:hover {
        background-color:rgb(42, 179, 0);
    }

    /* Alerts */
    .alert {
        padding: 10px;
        margin-bottom: 15px;
        border-radius: 4px;
        font-weight: bold;
    }

    .alert-success {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .alert-danger {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    /* Headings */
    h2 {
        margin-bottom: 20px;
        color: #333333;
    }

    /* Navigation Links */
    nav {
        margin-bottom: 20px;
        
    }

    nav a {
        display: inline-block;
        padding: 10px 15px;
        margin-right: 15px;
        text-decoration: none;
        color:rgb(0, 0, 0);
        background-color :rgb(81, 14, 168);
        font-weight: bold;
    }

    nav a:hover {
        text-decoration: underline;
        background-color: rgb(83, 168, 14);
    }
</style>

</head>
<body>
<div class="container">
    <nav>
        <a href="#upload">Upload Dokumen</a>
        <a href="#recent">Dokumen Terbaru</a>
    </nav>

    <h2 id="upload">Upload Dokumen</h2>
    <form action="" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="nama">Nama Dokumen:</label>
            <input type="text" name="nama" id="nama" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="deskripsi">Deskripsi:</label>
            <textarea name="deskripsi" id="deskripsi" class="form-control" rows="4"></textarea>
        </div>
        <div class="form-group">
            <label for="file">Pilih File:</label>
            <input type="file" name="file" id="file" class="form-control" required>
        </div>
        <button type="submit" name="upload" class="btn btn-primary">Upload</button>
    </form>

    <h2 id="recent">Dokumen Terbaru</h2>
    <?php if (!empty($dokumenTerbaru)): ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Deskripsi</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dokumenTerbaru as $dokumen): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($dokumen['nama']); ?></td>
                        <td><?php echo htmlspecialchars($dokumen['deskripsi']); ?></td>
                        <td>
                            <!-- Tombol untuk melihat dokumen -->
                            <a href="<?php echo htmlspecialchars($dokumen['lokasi_file']); ?>" target="_blank" class="btn btn-primary">Lihat</a>

                            <!-- Form untuk menghapus dokumen -->
                            <form action="" method="POST" style="display:inline;">
                                <input type="hidden" name="dokumen_id" value="<?php echo htmlspecialchars($dokumen['id']); ?>">
                                <button type="submit" name="delete" class="btn btn-danger">Hapus</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Tidak ada dokumen terbaru.</p>
    <?php endif; ?>
</div>
</body>
</html>
