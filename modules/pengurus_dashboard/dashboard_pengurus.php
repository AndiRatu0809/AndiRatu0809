<?php
session_start();
include dirname(__DIR__, 2) . '/config/db.php';

// Pastikan pengguna adalah pengurus dan telah login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pengurus') {
    header("Location: ../../dashboard_pengurus/login.php");
    exit();
}

// Tangani inisialisasi fullname
if (!isset($_SESSION['fullname'])) {
    try {
        $stmt = $pdo->prepare("SELECT fullname, email FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && isset($user['fullname'])) {
            $_SESSION['fullname'] = $user['fullname'];
            $_SESSION['email'] = $user['email'];
        } else {
            $_SESSION['fullname'] = "Pengurus";
        }
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}

// Ambil kegiatan terbaru
try {
    $stmt = $pdo->prepare("SELECT activity_name, activity_description, created_at, media_url FROM activities ORDER BY created_at DESC LIMIT 6");
    $stmt->execute();
    $recent_activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $recent_activities = [];
}

// Ambil statistik
try {
    $total_activities_stmt = $pdo->query("SELECT COUNT(*) FROM activities");
    $total_activities = $total_activities_stmt->fetchColumn();

    $total_documents_stmt = $pdo->query("SELECT COUNT(*) FROM documents");
    $total_documents = $total_documents_stmt->fetchColumn();
} catch (PDOException $e) {
    $total_activities = $total_documents = 0;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pengurus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
        }
        .sidebar {
            background-color: #2c3e50;
            color: white;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            width: 250px;
            transition: all 0.3s;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
            transition: all 0.3s;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .card:hover {
            transform: scale(1.03);
        }
        .sidebar-menu a {
            color: white;
            text-decoration: none;
            padding: 10px 15px;
            display: block;
            transition: background-color 0.3s;
        }
        .sidebar-menu a:hover {
            background-color: #34495e;
        }
        .activity-image {
            height: 200px;
            object-fit: cover;
        }
        .header-stats {
            background-color:rgb(40, 31, 179);
            color: white;
            padding: 20px;
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <div class="sidebar p-3">
        <h3 class="text-center mb-4">
            <i class="fas fa-user-shield me-2"></i>Dashboard Pengurus
        </h3>
        <div class="sidebar-menu">
            <a href="#" class="mb-2">
                <i class="fas fa-home me-2"></i>Beranda
            </a>
            <a href="../../modules/pengurus_dashboard/recent_activities.php" class="mb-2">
                <i class="fas fa-calendar-alt me-2"></i>unggah kegiatan
            </a>
            <!-- <a href="../../modules/pengurus_dashboard/view_activities.php" class="mb-2">
                <i class="fas fa-list me-2"></i>Semua Kegiatan
            </a> -->
            <a href="../../modules/pengurus_dashboard/upload_documents.php" class="mb-2">
                <i class="fas fa-file-alt me-2"></i>Dokumen
            </a>
            <!-- <a href="../../modules/pengurus_dashboard/profile.php" class="mb-2">
                <i class="fas fa-user me-2"></i>Profil -->
            </a>
            <a href="../auth/logout.php" class="mb-2 text-danger">
                <i class="fas fa-sign-out-alt me-2"></i>Logout
            </a>
        </div>
    </div>

    <div class="main-content">
        <div class="container-fluid">
            <div class="row mb-4">
                <div class="col-12 header-stats">
                    <h2>
                        <i class="fas fa-user me-2"></i>
                        Selamat datang, <?php echo htmlspecialchars($_SESSION['fullname']); ?>!
                    </h2>
                    <p class="mb-0">Anda memiliki akses sebagai Pengurus</p>
                </div>
            </div>

            <!-- <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <i class="fas fa-calendar-check fa-3x text-primary mb-3"></i>
                            <h5 class="card-title">Total Kegiatan</h5>
                            <p class="card-text display-6"><?php echo $total_activities; ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <i class="fas fa-file-pdf fa-3x text-success mb-3"></i>
                            <h5 class="card-title">Total Dokumen</h5>
                            <p class="card-text display-6"><?php echo $total_documents; ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <i class="fas fa-users fa-3x text-warning mb-3"></i>
                            <h5 class="card-title">Pengurus Aktif</h5>
                            <p class="card-text display-6">1</p>
                        </div>
                    </div>
                </div>
            </div> -->

            <div class="row">
                <div class="col-12">
                    <h3 class="mb-3">
                        <i class="fas fa-history me-2"></i>Kegiatan Terbaru
                    </h3>
                </div>
                <?php if (!empty($recent_activities)): ?>
                    <?php foreach($recent_activities as $activity): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card">
                                <?php 
                                if (!empty($activity['media_url'])): 
                                    $media_url = htmlspecialchars($activity['media_url']);
                                    if (preg_match('/\.(jpg|jpeg|png|gif)$/i', $media_url)): ?>
                                        <img src="<?php echo $media_url; ?>" 
                                             class="card-img-top activity-image" 
                                             alt="Kegiatan">
                                    <?php elseif (preg_match('/\.(mp4|webm|ogg)$/i', $media_url)): ?>
                                        <video class="card-img-top activity-image" controls>
                                            <source src="<?php echo $media_url; ?>" type="video/<?php echo pathinfo($media_url, PATHINFO_EXTENSION); ?>">
                                            Browser Anda tidak mendukung video.
                                        </video>
                                    <?php endif; 
                                endif; ?>
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($activity['activity_name']); ?></h5>
                                    <p class="card-text"><?php echo htmlspecialchars(substr($activity['activity_description'], 0, 100) . '...'); ?></p>
                                    <p class="card-text small text-muted">
                                        <i class="fas fa-calendar me-2"></i>
                                        <?php echo date("d M Y", strtotime($activity['created_at'])); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="alert alert-info">Belum ada kegiatan terbaru</div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
