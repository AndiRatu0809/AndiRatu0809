<?php
session_start();
include dirname(__DIR__, 2) . '/config/db.php';

// Pastikan pengguna adalah pengurus dan telah login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pengurus') {
    header("Location: ../../dashboard_pengurus/login.php");
    exit();
}

try {
    // Ambil data pengguna lengkap
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Hitung total kegiatan yang dibuat pengguna
    $activities_stmt = $pdo->prepare("SELECT COUNT(*) FROM activities WHERE created_by = ?");
    $activities_stmt->execute([$_SESSION['user_id']]);
    $total_activities = $activities_stmt->fetchColumn();

    // Hitung total dokumen yang diunggah pengguna
    $documents_stmt = $pdo->prepare("SELECT COUNT(*) FROM documents WHERE uploaded_by = ?");
    $documents_stmt->execute([$_SESSION['user_id']]);
    $total_documents = $documents_stmt->fetchColumn();
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Pengurus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .profile-header {
            background: linear-gradient(135deg, #4CAF50, #45a049);
            color: white;
            padding: 30px 0;
            text-align: center;
        }
        .profile-section {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 30px;
            margin-top: -50px;
            position: relative;
            z-index: 10;
        }
        .profile-stats {
            display: flex;
            justify-content: space-around;
            margin-top: 20px;
        }
        .stat-item {
            text-align: center;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 10px;
        }
        .edit-profile-btn {
            position: absolute;
            top: 20px;
            right: 20px;
        }
        @media (max-width: 768px) {
            .profile-stats {
                flex-direction: column;
                gap: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="profile-header">
            <div class="container">
                <img src="/api/placeholder/150/150" class="rounded-circle mb-3" alt="Foto Profil">
                <h2><?php echo htmlspecialchars($user['fullname']); ?></h2>
                <p><?php echo htmlspecialchars($user['email']); ?></p>
            </div>
        </div>

        <div class="container">
            <div class="profile-section position-relative">
                <button class="btn btn-primary edit-profile-btn" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                    <i class="bi bi-pencil"></i> Edit Profil
                </button>

                <h3 class="mb-4">Informasi Pribadi</h3>
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Nama Lengkap:</strong> <?php echo htmlspecialchars($user['fullname']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                        <p><strong>Bergabung Sejak:</strong> <?php echo date('d M Y', strtotime($user['created_at'])); ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Role:</strong> <?php echo htmlspecialchars($user['role']); ?></p>
                        <p><strong>Status Akun:</strong> Aktif</p>
                    </div>
                </div>

                <div class="profile-stats">
                    <div class="stat-item">
                        <h4><?php echo $total_activities; ?></h4>
                        <p>Total Kegiatan</p>
                    </div>
                    <div class="stat-item">
                        <h4><?php echo $total_documents; ?></h4>
                        <p>Total Dokumen</p>
                    </div>
                    <div class="stat-item">
                        <h4>0</h4>
                        <p>Pencapaian</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Edit Profil -->
    <div class="modal fade" id="editProfileModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Edit Profil</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="update_profile.php" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" name="fullname" class="form-control" value="<?php echo htmlspecialchars($user['fullname']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Foto Profil</label>
                            <input type="file" name="profile_photo" class="form-control" accept="image/*">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Ganti Password Baru (Opsional)</label>
                            <input type="password" name="new_password" class="form-control" placeholder="Kosongkan jika tidak ingin mengganti">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password Lama (Wajib untuk konfirmasi perubahan)</label>
                            <input type="password" name="old_password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-success w-100">Perbarui Profil</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</body>
</html>