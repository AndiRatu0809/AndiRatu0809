<?php
session_start();
include '../../config/db.php';

// Periksa apakah pengguna telah login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../modules/auth/login.php");
    exit();
}

// Ambil semua data pengurus dari tabel
$stmtPengurus = $pdo->prepare("SELECT p.id, p.nah, p.nama_lengkap, p.jenis_kelamin, p.tempat_lahir, 
    p.tanggal_lahir, p.alamat, p.telepon, p.email, p.program_studi, p.angkatan, p.status, 
    p.jabatan, p.tanggal_daftar, p.is_verified 
    FROM pengurus p");
$stmtPengurus->execute();
$pengurus = $stmtPengurus->fetchAll(PDO::FETCH_ASSOC);

// Proses untuk tambah atau edit data
$edit_user = [];
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $edit_stmt = $pdo->prepare("SELECT * FROM pengurus WHERE id = ?");
    $edit_stmt->execute([$id]);
    $edit_user = $edit_stmt->fetch(PDO::FETCH_ASSOC);
}

// Simpan data pengurus (tambah atau edit)
if (isset($_POST['save_user'])) {
    $id = $_POST['id'] ?? null;
    $nah = $_POST['nah'];
    $nama_lengkap = $_POST['nama_lengkap'];
    $jenis_kelamin = $_POST['jenis_kelamin'];
    $tempat_lahir = $_POST['tempat_lahir'];
    $tanggal_lahir = $_POST['tanggal_lahir'];
    $alamat = $_POST['alamat'];
    $telepon = $_POST['telepon'];
    $email = $_POST['email'];
    $program_studi = $_POST['program_studi'];
    $angkatan = $_POST['angkatan'];
    $status = $_POST['status'];
    $jabatan = $_POST['jabatan'];
    $is_verified = isset($_POST['is_verified']) ? 1 : 0;
    
    if ($id) { // Update data
        $update_stmt = $pdo->prepare("UPDATE pengurus SET 
            nah = ?, nama_lengkap = ?, jenis_kelamin = ?, tempat_lahir = ?, 
            tanggal_lahir = ?, alamat = ?, telepon = ?, email = ?, 
            program_studi = ?, angkatan = ?, status = ?, jabatan = ?, 
            is_verified = ? WHERE id = ?");
        $update_stmt->execute([$nah, $nama_lengkap, $jenis_kelamin, $tempat_lahir, 
            $tanggal_lahir, $alamat, $telepon, $email, $program_studi, 
            $angkatan, $status, $jabatan, $is_verified, $id]);
    } else { // Tambah data baru
        $insert_stmt = $pdo->prepare("INSERT INTO pengurus 
            (nah, nama_lengkap, jenis_kelamin, tempat_lahir, tanggal_lahir, 
            alamat, telepon, email, program_studi, angkatan, status, jabatan, 
            is_verified, tanggal_daftar) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $insert_stmt->execute([$nah, $nama_lengkap, $jenis_kelamin, $tempat_lahir, 
            $tanggal_lahir, $alamat, $telepon, $email, $program_studi, 
            $angkatan, $status, $jabatan, $is_verified]);
    }

    header("Location: edit_delete_pengurus.php?success=1");
    exit();
}

// Hapus data pengurus
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    if (is_numeric($delete_id)) {
        $delete_stmt = $pdo->prepare("DELETE FROM pengurus WHERE id = ?");
        $delete_stmt->execute([$delete_id]);
    }
    header("Location: edit_delete_pengurus.php");
    exit();
}

// Verifikasi pengurus
if (isset($_GET['verify_id'])) {
    $verify_id = $_GET['verify_id'];
    if (is_numeric($verify_id)) {
        $verify_stmt = $pdo->prepare("UPDATE pengurus SET is_verified = 1 WHERE id = ?");
        $verify_stmt->execute([$verify_id]);
    }
    header("Location: edit_delete_pengurus.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Pengurus</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        /* ... (style yang sama seperti sebelumnya) ... */
        :root {
            --primary-color: #4a90e2;
            --secondary-color: #2ecc71;
            --danger-color: #e74c3c;
            --text-color: #333;
            --light-gray: #f5f6fa;
            --border-color: #dcdde1;
            --shadow-color: rgba(0, 0, 0, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Styling Dasar */
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f0f2f5;
            color: var(--text-color);
            line-height: 1.6;
        }

        /* Container Utama */
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 2rem;
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 5px 15px var(--shadow-color);
        }

        /* Header dan Navigasi */
        .button-back {
            display: inline-block;
            padding: 0.8rem 1.5rem;
            background-color: var(--primary-color);
            color: white;
            text-decoration: none;
            border-radius: 6px;
            transition: all 0.3s ease;
            margin-bottom: 2rem;
        }

        .button-back:hover {
            background-color: #357abd;
            transform: translateY(-2px);
        }

        /* Headings */
        h2, h3 {
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            font-weight: 600;
        }

        /* Form Styling */
        form {
            background-color: var(--light-gray);
            padding: 2rem;
            border-radius: 8px;
            margin-bottom: 2rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--text-color);
        }

        input[type="text"],
        input[type="email"],
        input[type="date"],
        select,
        textarea {
            width: 100%;
            padding: 0.8rem;
            margin-bottom: 1rem;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            transition: border-color 0.3s ease;
        }

        input:focus,
        select:focus,
        textarea:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 2px rgba(74, 144, 226, 0.2);
        }

        /* Checkbox Styling */
        .checkbox-wrapper {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }

        input[type="checkbox"] {
            margin-right: 0.5rem;
        }

        /* Button Styling */
        .submit-btn {
            background-color: var(--secondary-color);
            color: white;
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .submit-btn:hover {
            background-color: #27ae60;
            transform: translateY(-2px);
        }

        /* Table Styling */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1.5rem;
            background-color: white;
            box-shadow: 0 2px 8px var(--shadow-color);
        }

        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }

        th {
            background-color: var(--primary-color);
            color: white;
            font-weight: 500;
        }

        tr:hover {
            background-color: var(--light-gray);
        }

        /* Status Badges */
        .verified,
        .unverified {
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .verified {
            background-color: var(--secondary-color);
            color: white;
        }

        .unverified {
            background-color: var(--danger-color);
            color: white;
        }

        /* Action Buttons */
        .action-buttons button {
            padding: 0.5rem 1rem;
            margin: 0 0.2rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }

        .edit-btn {
            background-color: var(--primary-color);
            color: white;
        }

        .delete-btn {
            background-color: var(--danger-color);
            color: white;
        }

        .verify-btn {
            background-color: var(--secondary-color);
            color: white;
        }

        .action-buttons button:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        /* Success Message */
        .success-message {
            background-color: var(--secondary-color);
            color: white;
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                margin: 1rem;
                padding: 1rem;
            }

            table {
                display: block;
                overflow-x: auto;
            }

            th, td {
                padding: 0.8rem;
            }

            .action-buttons button {
                margin-bottom: 0.5rem;
                width: 100%;
            }
        }
        .verified {
            background-color: #2ecc71;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
        }
        .unverified {
            background-color: #e74c3c;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
        }
    </style>
</head>
<body>
<div class="container">
    <a href="../dashboard/admin_dashboard.php" class="button-back">Kembali ke Dashboard</a>
    
    <h2><?= isset($_GET['id']) ? "Edit Data Pengurus" : "Tambah Data Pengurus" ?></h2>
    
    <form action="edit_delete_pengurus.php" method="POST">
        <input type="hidden" name="id" value="<?= htmlspecialchars($edit_user['id'] ?? '') ?>">
        
        <!-- Field yang sama seperti sebelumnya -->
        <label for="nah">NAH:</label>
        <input type="text" id="nah" name="nah" value="<?= htmlspecialchars($edit_user['nah'] ?? '') ?>" required>
        
        <!-- ... (field lainnya) ... -->

        <label for="jabatan">Jabatan:</label>
        <select id="jabatan" name="jabatan" required>
            <option value="Ketua" <?= (isset($edit_user['jabatan']) && $edit_user['jabatan'] == 'Ketua') ? 'selected' : '' ?>>Ketua</option>
            <option value="Wakil Ketua" <?= (isset($edit_user['jabatan']) && $edit_user['jabatan'] == 'Wakil Ketua') ? 'selected' : '' ?>>Wakil Ketua</option>
            <option value="Sekretaris" <?= (isset($edit_user['jabatan']) && $edit_user['jabatan'] == 'Sekretaris') ? 'selected' : '' ?>>Sekretaris</option>
            <option value="Bendahara" <?= (isset($edit_user['jabatan']) && $edit_user['jabatan'] == 'Bendahara') ? 'selected' : '' ?>>Bendahara</option>
            <option value="Anggota" <?= (isset($edit_user['jabatan']) && $edit_user['jabatan'] == 'Anggota') ? 'selected' : '' ?>>Anggota</option>
        </select>

        <label for="is_verified">Status Verifikasi:</label>
        <input type="checkbox" id="is_verified" name="is_verified" <?= (isset($edit_user['is_verified']) && $edit_user['is_verified'] == 1) ? 'checked' : '' ?>>
        
        <button type="submit" name="save_user" class="submit-btn">Simpan</button>
    </form>

    <?php if (isset($_GET['success'])) : ?>
        <div style='color:#2ecc71;'>Data berhasil disimpan!</div>
    <?php endif; ?>

    <h3>Data Pengurus</h3>
    <table>
        <tr>
            <th>ID</th>
            <th>NAH</th>
            <th>Nama Lengkap</th>
            <th>Jabatan</th>
            <th>Status Verifikasi</th>
            <th>Tanggal Daftar</th>
            <th>Aksi</th>
        </tr>
        <?php foreach ($pengurus as $user): ?>
        <tr>
            <td><?= htmlspecialchars($user['id']) ?></td>
            <td><?= htmlspecialchars($user['nah']) ?></td>
            <td><?= htmlspecialchars($user['nama_lengkap']) ?></td>
            <td><?= htmlspecialchars($user['jabatan']) ?></td>
            <td>
                <?php if ($user['is_verified']): ?>
                    <span class="verified">Terverifikasi</span>
                <?php else: ?>
                    <span class="unverified">Belum Terverifikasi</span>
                <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($user['tanggal_daftar']) ?></td>
            <td>
                <button onclick="editUser(<?= htmlspecialchars($user['id']) ?>)">Edit</button>
                <button onclick="confirmDelete(<?= htmlspecialchars($user['id']) ?>)">Hapus</button>
                <?php if (!$user['is_verified']): ?>
                    <button onclick="verifyUser(<?= htmlspecialchars($user['id']) ?>)">Verifikasi</button>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<script>
    function editUser(id) {
        window.location.href = `tambah_anggota.php?id=${id}`;
    }

    function confirmDelete(id) {
        if (confirm("Apakah Anda yakin ingin menghapus pengurus ini?")) {
            window.location.href = `edit_delete_pengurus.php?delete_id=${id}`;
        }
    }

    function verifyUser(id) {
        if (confirm("Apakah Anda yakin ingin memverifikasi pengurus ini?")) {
            window.location.href = `edit_delete_pengurus.php?verify_id=${id}`;
        }
    }
</script>

</body>
</html>