<?php
session_start();
include dirname(__DIR__, 2) . '/config/db.php';

// Pastikan pengguna adalah pengurus dan telah login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pengurus') {
    header("Location: ../../dashboard_pengurus/login.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $activity_name = $_POST['activity_name'] ?? '';
    $activity_description = $_POST['activity_description'] ?? '';
    $media_url = null;

    // Validasi data
    if (empty($activity_name) || empty($activity_description)) {
        $error = "Nama kegiatan dan deskripsi wajib diisi.";
    } elseif (!empty($_FILES['media']['name'])) {
        // Proses upload file
        $target_dir = dirname(__DIR__, 2) . "/uploads/";
        $file_name = basename($_FILES['media']['name']);
        $target_file = $target_dir . $file_name;
        $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Validasi jenis file
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'mp4', 'webm', 'ogg'];
        if (!in_array($file_type, $allowed_types)) {
            $error = "Format file tidak didukung. Hanya file JPG, PNG, GIF, MP4, WEBM, atau OGG yang diperbolehkan.";
        } elseif (move_uploaded_file($_FILES['media']['tmp_name'], $target_file)) {
            $media_url = "../../uploads/" . $file_name; // Path relatif
        } else {
            $error = "Terjadi kesalahan saat mengupload file.";
        }
    }

    // Simpan ke database jika tidak ada error
    if (empty($error)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO activities (activity_name, activity_description, media_url, created_at) 
                VALUES (:activity_name, :activity_description, :media_url, NOW())
            ");
            $stmt->execute([
                ':activity_name' => $activity_name,
                ':activity_description' => $activity_description,
                ':media_url' => $media_url,
            ]);
            $success = "Kegiatan berhasil ditambahkan.";
        } catch (PDOException $e) {
            $error = "Terjadi kesalahan: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Kegiatan</title>
    <style>
        /* Tambahkan styling sederhana */
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .form-container {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            margin: auto;
        }
        h1 {
            text-align: center;
            color: #4CAF50;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            font-weight: bold;
        }
        input, textarea, button {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            background-color: #4CAF50;
            color: white;
            font-size: 16px;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
        .error, .success {
            text-align: center;
            margin-bottom: 15px;
        }
        .error {
            color: red;
        }
        .success {
            color: green;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h1>Tambah Kegiatan</h1>

        <?php if (!empty($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>

        <form action="" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="activity_name">Nama Kegiatan</label>
                <input type="text" name="activity_name" id="activity_name" required>
            </div>
            <div class="form-group">
                <label for="activity_description">Deskripsi Kegiatan</label>
                <textarea name="activity_description" id="activity_description" rows="5" required></textarea>
            </div>
            <div class="form-group">
                <label for="media">Media (opsional)</label>
                <input type="file" name="media" id="media" accept=".jpg, .jpeg, .png, .gif, .mp4, .webm, .ogg">
            </div>
            <button type="submit">Simpan</button>
        </form>
    </div>
</body>
</html>
