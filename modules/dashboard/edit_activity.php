<?php
session_start();
include '../../config/db.php';

// Mengecek apakah pengguna sudah login dan memiliki peran admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {
    $activity_id = $_GET['id'];

    // Mengambil data kegiatan yang akan diedit
    $stmt = $pdo->prepare("SELECT * FROM activity WHERE id = ?");
    $stmt->execute([$activity_id]);
    $activity = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Mengupdate data kegiatan
        $title = $_POST['title'];
        $description = $_POST['description'];

        $stmt = $pdo->prepare("UPDATE activities SET title = ?, description = ? WHERE id = ?");
        $stmt->execute([$title, $description, $activity_id]);

        header("Location: view_activity.php"); // Kembali ke halaman kegiatan setelah edit
        exit();
    }
} else {
    echo "Kegiatan tidak ditemukan.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Kegiatan</title>
</head>
<body>

    <h2>Edit Kegiatan</h2>

    <form method="POST">
        <label for="title">Judul Kegiatan:</label>
        <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($activity['title']); ?>" required><br>

        <label for="description">Deskripsi:</label>
        <textarea id="description" name="description" required><?php echo htmlspecialchars($activity['description']); ?></textarea><br>

        <button type="submit">Simpan Perubahan</button>
    </form>

</body>
</html>
