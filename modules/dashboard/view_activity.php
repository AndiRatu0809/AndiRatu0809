<?php
session_start();
include dirname(__DIR__, 2) . '/config/db.php'; // Menghubungkan ke database

// Cek apakah pengguna sudah login dan memiliki peran admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Tentukan batasan jumlah kegiatan per halaman
$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

try {
    // Ambil data kegiatan dari database
    $stmt = $pdo->prepare("
        SELECT activities.*, users.fullname 
        FROM activities
        JOIN users ON activities.created_by = users.id
        WHERE users.role = 'pengurus'
        ORDER BY activities.created_at DESC
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $activities = $stmt->fetchAll();

    // Ambil total kegiatan untuk pagination
    $total_stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM activities 
        JOIN users ON activities.created_by = users.id 
        WHERE users.role = 'pengurus'
    ");
    $total_stmt->execute();
    $total_activities = $total_stmt->fetchColumn();
    $total_pages = ceil($total_activities / $limit);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Kegiatan Pengurus</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* Tambahan CSS untuk mempercantik tampilan */
        .container {
            max-width: 1200px;
            margin: auto;
            padding: 20px;
        }

        .activity-uploaded-by {
            font-size: 0.9em;
            color: #555;
            margin-top: 10px;
            font-style: italic;
        }

        .activity-card {
            background-color: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .activity-card:hover {
            transform: translateY(-5px);
        }

        .activity-title {
            font-size: 1.2em;
            color: #2c3e50;
            font-weight: 600;
        }

        .activity-description {
            color: #666;
            font-size: 0.9em;
            margin-bottom: 10px;
            line-height: 1.6;
        }

        .activity-media {
            width: 100%;
            height: 200px;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .activity-media img, .activity-media video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .no-media {
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #f0f0f0;
            color: #888;
            height: 200px;
            font-size: 24px;
        }

        .activity-meta {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
            border-top: 1px solid #eee;
            padding-top: 10px;
            font-size: 0.8em;
            color: #888;
        }

        .activities-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 30px;
            gap: 10px;
        }

        .pagination a, .pagination span {
            padding: 8px 12px;
            border: 1px solid #4CAF50;
            border-radius: 4px;
            text-decoration: none;
            color: #4CAF50;
            background: white;
        }

        .pagination .current {
            background-color: #4CAF50;
            color: white;
        }

        .pagination a:hover {
            background-color: #4CAF50;
            color: white;
        }

        .button-back {
    display: inline-block;
    padding: 10px 20px;
    font-size: 16px;
    color: white;
    background-color:rgb(38, 12, 80); /* Hijau lembut */
    border: none;
    border-radius: 5px;
    text-decoration: none;
    font-family: 'Arial', sans-serif;
    font-weight: bold;
    text-align: center;
    transition: all 0.3s ease;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
}

.button-back:hover {
    background-color:rgb(40, 15, 78); /* Hijau lebih gelap saat hover */
    transform: translateY(-2px);
    box-shadow: 0 6px 8px rgba(0, 0, 0, 0.3);
}

.button-back:active {
    transform: translateY(1px);
    box-shadow: 0 3px 4px rgba(0, 0, 0, 0.2);
}

    </style>
</head>
<body>
    <div class="container">
        <h1>Dashboard Admin - Kegiatan Pengurus</h1>

        <!-- Menampilkan Kegiatan -->
        <div class="activities-grid">
            <?php if (count($activities) > 0): ?>
                <?php foreach ($activities as $activity): ?>
                    <div class="activity-card">
                        <div class="activity-media">
                            <?php if (!empty($activity['media_url'])): ?>
                                <?php
                                $file_extension = strtolower(pathinfo($activity['media_url'], PATHINFO_EXTENSION));
                                if (in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif'])): ?>
                                    <img src="<?php echo htmlspecialchars($activity['media_url']); ?>" 
                                         alt="<?php echo htmlspecialchars($activity['activity_name']); ?>">
                                <?php elseif (in_array($file_extension, ['mp4', 'webm', 'ogg'])): ?>
                                    <video controls>
                                        <source src="<?php echo htmlspecialchars($activity['media_url']); ?>" 
                                                type="video/<?php echo $file_extension; ?>">
                                        Browser Anda tidak mendukung pemutaran video.
                                    </video>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="no-media">
                                    <i class="fas fa-image fa-2x"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="activity-content">
                            <h3 class="activity-title"><?php echo htmlspecialchars($activity['activity_name']); ?></h3>
                            <p class="activity-description"><?php echo htmlspecialchars($activity['activity_description']); ?></p>
                            <p class="activity-uploaded-by">
                                <strong>Diunggah oleh:</strong> <?php echo htmlspecialchars($activity['fullname']); ?>
                            </p>
                            <div class="activity-meta">
                                <p class="activity-date">
                                    <i class="far fa-calendar"></i>
                                    <?php echo date('d/m/Y H:i', strtotime($activity['created_at'])); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Belum ada kegiatan yang diunggah oleh pengurus.</p>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="view_activity.php?page=<?php echo $page - 1; ?>">Previous</a>
            <?php endif; ?>
            <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                <a href="view_activity.php?page=<?php echo $p; ?>" 
                   class="<?php echo ($p == $page) ? 'current' : ''; ?>">
                    <?php echo $p; ?>
                </a>
            <?php endfor; ?>
            <?php if ($page < $total_pages): ?>
                <a href="view_activity.php?page=<?php echo $page + 1; ?>">Next</a>
            <?php endif; ?>
        </div>
        <a href="../dashboard/admin_dashboard.php" class="button-back">Kembali ke Dashboard</a>
    </div>
</body>
</html>
