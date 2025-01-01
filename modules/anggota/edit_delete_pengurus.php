<?php
session_start();
include '../../config/db.php';

// Cek login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../modules/auth/login.php");
    exit();
}

// Get pengurus data with role-based visibility
$user_role = $_SESSION['role'] ?? 'anggota';
$stmt_pengurus = $pdo->prepare("
    SELECT p.*, 
           CASE 
               WHEN ? = 'admin' THEN p.email 
               ELSE CONCAT(SUBSTRING(p.email, 1, 3), '***@', SUBSTRING_INDEX(p.email, '@', -1))
           END as displayed_email
    FROM pengurus p
");
$stmt_pengurus->execute([$user_role]);
$pengurus = $stmt_pengurus->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        if (isset($_POST['save_pengurus'])) {
            $id = $_POST['id'] ?? null;
            $data = [
                'nah' => filter_input(INPUT_POST, 'nah', FILTER_SANITIZE_STRING),
                'nama_lengkap' => filter_input(INPUT_POST, 'nama_lengkap', FILTER_SANITIZE_STRING),
                'jenis_kelamin' => filter_input(INPUT_POST, 'jenis_kelamin', FILTER_SANITIZE_STRING),
                'tempat_lahir' => filter_input(INPUT_POST, 'tempat_lahir', FILTER_SANITIZE_STRING),
                'tanggal_lahir' => filter_input(INPUT_POST, 'tanggal_lahir', FILTER_SANITIZE_STRING),
                'alamat' => filter_input(INPUT_POST, 'alamat', FILTER_SANITIZE_STRING),
                'telepon' => filter_input(INPUT_POST, 'telepon', FILTER_SANITIZE_STRING),
                'email' => filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL),
                'program_studi' => filter_input(INPUT_POST, 'program_studi', FILTER_SANITIZE_STRING),
                'angkatan' => filter_input(INPUT_POST, 'angkatan', FILTER_SANITIZE_STRING),
                'status' => filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING),
                'jabatan' => filter_input(INPUT_POST, 'jabatan', FILTER_SANITIZE_STRING),
                'is_verified' => isset($_POST['is_verified']) ? 1 : 0
            ];

            // Validasi data
            foreach ($data as $key => $value) {
                if (empty($value) && $key != 'is_verified') {
                    throw new Exception("Field $key tidak boleh kosong");
                }
            }

            // Cek email unik
            $email_check = $pdo->prepare("SELECT id FROM pengurus WHERE email = ? AND id != ?");
            $email_check->execute([$data['email'], $id ?? 0]);
            if ($email_check->rowCount() > 0) {
                throw new Exception("Email sudah digunakan");
            }

            if ($id) {
                // Update existing pengurus
                $sql = "UPDATE pengurus SET 
                        nah = ?, nama_lengkap = ?, jenis_kelamin = ?, 
                        tempat_lahir = ?, tanggal_lahir = ?, alamat = ?, 
                        telepon = ?, email = ?, program_studi = ?, 
                        angkatan = ?, status = ?, jabatan = ?, is_verified = ? 
                        WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $data['nah'], $data['nama_lengkap'], $data['jenis_kelamin'],
                    $data['tempat_lahir'], $data['tanggal_lahir'], $data['alamat'],
                    $data['telepon'], $data['email'], $data['program_studi'],
                    $data['angkatan'], $data['status'], $data['jabatan'], 
                    $data['is_verified'], $id
                ]);
                $_SESSION['success'] = "Data pengurus berhasil diperbarui";
            } else {
                // Insert new pengurus
                $sql = "INSERT INTO pengurus (
                            nah, nama_lengkap, jenis_kelamin, tempat_lahir, 
                            tanggal_lahir, alamat, telepon, email, program_studi, 
                            angkatan, status, jabatan, is_verified, tanggal_daftar
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $data['nah'], $data['nama_lengkap'], $data['jenis_kelamin'],
                    $data['tempat_lahir'], $data['tanggal_lahir'], $data['alamat'],
                    $data['telepon'], $data['email'], $data['program_studi'],
                    $data['angkatan'], $data['status'], $data['jabatan'], 
                    $data['is_verified']
                ]);
                $_SESSION['success'] = "Data pengurus berhasil ditambahkan";
            }
        }
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
    
    header("Location: edit_delete_pengurus.php");
    exit();
}

// Handle delete request
if (isset($_GET['delete'])) {
    $id = filter_input(INPUT_GET, 'delete', FILTER_VALIDATE_INT);
    if ($id) {
        try {
            $stmt = $pdo->prepare("DELETE FROM pengurus WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['success'] = "Data pengurus berhasil dihapus";
        } catch (Exception $e) {
            $_SESSION['error'] = "Gagal menghapus data: " . $e->getMessage();
        }
    }
    header("Location: edit_delete_pengurus.php");
    exit();
}

// Get single pengurus data for editing
$edit_data = null;
if (isset($_GET['edit'])) {
    $id = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT);
    if ($id) {
        $stmt = $pdo->prepare("SELECT * FROM pengurus WHERE id = ?");
        $stmt->execute([$id]);
        $edit_data = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Pengurus</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
    body {
        font-family: 'Poppins', sans-serif;
        background-color: #f4f6f9;
        color: #333;
        margin: 0;
        padding: 0;
    }

    .container {
        max-width: 1200px;
        margin: 20px auto;
        padding: 20px;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .top-nav {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .top-nav h1 {
        font-size: 24px;
        color: #4a90e2;
        margin: 0;
    }

    .top-nav a.back-button {
        text-decoration: none;
        color: #4a90e2;
        font-weight: 600;
    }

    .top-nav a.back-button i {
        margin-right: 5px;
    }

    .alert {
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 20px;
    }

    .alert-danger {
        background-color: #ffdddd;
        color: #d9534f;
    }

    .alert-success {
        background-color: #ddffdd;
        color: #5cb85c;
    }

    .pengurus-form .form-group,
    .form-row .form-group {
        margin-bottom: 15px;
    }

    .pengurus-form label {
        font-weight: 500;
        margin-bottom: 5px;
        display: block;
    }

    .pengurus-form input,
    .pengurus-form select,
    .pengurus-form textarea {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 14px;
    }

    .pengurus-form textarea {
        height: 80px;
        resize: none;
    }

    .pengurus-form .form-row {
        display: flex;
        gap: 15px;
    }

    .pengurus-form .checkbox-group label {
        display: flex;
        align-items: center;
    }

    .pengurus-form .checkbox-group input {
        margin-right: 10px;
    }

    .form-actions {
        margin-top: 20px;
    }

    .form-actions .btn {
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        font-size: 14px;
        cursor: pointer;
        transition: background-color 0.3s;
    }

    .btn-primary {
        background-color: #4a90e2;
        color: white;
    }

    .btn-primary:hover {
        background-color: #357abd;
    }

    .btn-secondary {
        background-color: #ddd;
        color: #333;
    }

    .btn-secondary:hover {
        background-color: #ccc;
    }

    .table-container {
        margin-top: 30px;
        overflow-x: auto;
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }

    .data-table thead tr {
        background-color: #4a90e2;
        color: white;
    }

    .data-table th,
    .data-table td {
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }

    .data-table tbody tr:hover {
        background-color: #f1f1f1;
    }

    .data-table .badge {
        padding: 5px 10px;
        border-radius: 3px;
        font-size: 12px;
        text-transform: uppercase;
    }

    .badge-success {
        background-color: #5cb85c;
        color: white;
    }

    .badge-danger {
        background-color: #d9534f;
        color: white;
    }

    .badge-primary {
        background-color: #337ab7;
        color: white;
    }

    .badge-secondary {
        background-color: #ccc;
        color: #333;
    }

    .btn-sm {
        padding: 5px 10px;
        font-size: 12px;
        border-radius: 3px;
    }

    .btn-warning {
        background-color: #f0ad4e;
        color: white;
    }

    .btn-warning:hover {
        background-color: #ec971f;
    }

    .btn-danger {
        background-color: #d9534f;
        color: white;
    }

    .btn-danger:hover {
        background-color: #c9302c;
    }
</style>

</head>
<body>
    <div class="container">
        <nav class="top-nav">
            <a href="../dashboard/admin_dashboard.php" class="back-button">
                <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
            </a>
            <h1><?= $edit_data ? 'Edit Pengurus' : 'Tambah Pengurus Baru' ?></h1>
        </nav>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?= $_SESSION['error'] ?>
                <?php unset($_SESSION['error']) ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?= $_SESSION['success'] ?>
                <?php unset($_SESSION['success']) ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="pengurus-form" id="pengurusForm">
            <input type="hidden" name="id" value="<?= $edit_data['id'] ?? '' ?>">
            
            <div class="form-group">
                <label for="nah">Nomor Anggota (NAH)</label>
                <input type="text" id="nah" name="nah" value="<?= $edit_data['nah'] ?? '' ?>" required>
            </div>

            <div class="form-group">
                <label for="nama_lengkap">Nama Lengkap</label>
                <input type="text" id="nama_lengkap" name="nama_lengkap" value="<?= $edit_data['nama_lengkap'] ?? '' ?>" required>
            </div>

            <div class="form-group">
                <label for="jenis_kelamin">Jenis Kelamin</label>
                <select id="jenis_kelamin" name="jenis_kelamin" required>
                    <option value="">Pilih Jenis Kelamin</option>
                    <option value="Laki-laki" <?= ($edit_data['jenis_kelamin'] ?? '') == 'Laki-laki' ? 'selected' : '' ?>>Laki-laki</option>
                    <option value="Perempuan" <?= ($edit_data['jenis_kelamin'] ?? '') == 'Perempuan' ? 'selected' : '' ?>>Perempuan</option>
                </select>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="tempat_lahir">Tempat Lahir</label>
                    <input type="text" id="tempat_lahir" name="tempat_lahir" value="<?= $edit_data['tempat_lahir'] ?? '' ?>" required>
                </div>

                <div class="form-group">
                    <label for="tanggal_lahir">Tanggal Lahir</label>
                    <input type="date" id="tanggal_lahir" name="tanggal_lahir" value="<?= $edit_data['tanggal_lahir'] ?? '' ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label for="alamat">Alamat</label>
                <textarea id="alamat" name="alamat" required><?= $edit_data['alamat'] ?? '' ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="telepon">Nomor Telepon</label>
                    <input type="tel" id="telepon" name="telepon" value="<?= $edit_data['telepon'] ?? '' ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?= $edit_data['email'] ?? '' ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="program_studi">Program Studi</label>
                    <input type="text" id="program_studi" name="program_studi" value="<?= $edit_data['program_studi'] ?? '' ?>" required>
                </div>

                <div class="form-group">
                    <label for="angkatan">Angkatan</label>
                    <input type="text" id="angkatan" name="angkatan" value="<?= $edit_data['angkatan'] ?? '' ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status" required>
                        <option value="Aktif" <?= ($edit_data['status'] ?? '') == 'Aktif' ? 'selected' : '' ?>>Aktif</option>
                        <option value="Tidak Aktif" <?= ($edit_data['status'] ?? '') == 'Tidak Aktif' ? 'selected' : '' ?>>Tidak Aktif</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="jabatan">Jabatan</label>
                    <select id="jabatan" name="jabatan" required>
                        <option value="Ketua" <?= ($edit_data['jabatan'] ?? '') == 'Ketua' ? 'selected' : '' ?>>Ketua</option>
                        <option value="Wakil Ketua" <?= ($edit_data['jabatan'] ?? '') == 'Wakil Ketua' ? 'selected' : '' ?>>Wakil Ketua</option>
                        <option value="Sekretaris" <?= ($edit_data['jabatan'] ?? '') == 'Sekretaris' ? 'selected' : '' ?>>Sekretaris</option>
                        <option value="Bendahara" <?= ($edit_data['jabatan'] ?? '') == 'Bendahara' ? 'selected' : '' ?>>Bendahara</option>
                        <option value="Anggota" <?= ($edit_data['jabatan'] ?? '') == 'Anggota' ? 'selected' : '' ?>>Anggota</option>
                    </select>
                </div>
            </div>

            <div class="form-group checkbox-group">
                <label>
                    <input type="checkbox" name="is_verified" <?= ($edit_data['is_verified'] ?? 0) == 1 ? 'checked' : '' ?>>
                    Terverifikasi
                </label>
            </div>

            <div class="form-actions">
                <button type="submit" name="save_pengurus" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan
                </button>
                <a href="edit_delete_pengurus.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Batal
                </a>
            </div>
        </form>

        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>NAH</th>
                        <th>Nama</th>
                        <th>Jabatan</th>
                        <th>Status</th>
                        <th>Verifikasi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pengurus as $p): ?>
                    <tr>
                        <td><?= htmlspecialchars($p['nah']) ?></td>
                        <td><?= htmlspecialchars($p['nama_lengkap']) ?></td>
                        <td><?= htmlspecialchars($p['jabatan']) ?></td>
                        <td>
                            <span class="badge <?= $p['status'] == 'Aktif' ? 'badge-success' : 'badge-danger' ?>">
                                <?= htmlspecialchars($p['status']) ?>
                                </span>
                        </td>
                        <td>
                            <span class="badge <?= $p['is_verified'] ? 'badge-primary' : 'badge-secondary' ?>">
                                <?= $p['is_verified'] ? 'Ya' : 'Tidak' ?>
                            </span>
                        </td>
                        <td>
                            <a href="edit_delete_pengurus.php?edit=<?= $p['id'] ?>" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="edit_delete_pengurus.php?delete=<?= $p['id'] ?>" class="btn btn-danger btn-sm" 
                               onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?');">
                                <i class="fas fa-trash"></i> Hapus
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
