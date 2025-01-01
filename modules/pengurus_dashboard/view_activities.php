<?php
session_start();
include dirname(__DIR__, 2) . '/config/db.php';

// Pastikan pengguna adalah pengurus dan telah login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pengurus') {
    header("Location: ../../dashboard_pengurus/login.php");
    exit();
}

// Konfigurasi pagination
$records_per_page = 12; // Menampilkan 12 kegiatan per halaman
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

try {
    // Hitung total kegiatan
    $count_stmt = $pdo->prepare("SELECT COUNT(*) FROM activities");
    $count_stmt->execute();
    $total_activities = $count_stmt->fetchColumn();
    $total_pages = ceil($total_activities / $records_per_page);

    // Query untuk mendapatkan kegiatan lama dengan pagination
    $stmt = $pdo->prepare("
        SELECT * FROM activities 
        WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)
        ORDER BY created_at DESC 
        LIMIT :limit OFFSET :offset
    ");
    
    $stmt->bindValue(':limit', $records_per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arsip Kegiatan</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            color: #333;
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .header h1 {
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .activities-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            padding: 20px 0;
        }

        .activity-card {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }

        .activity-card:hover {
            transform: translateY(-5px);
        }

        .activity-media {
            width: 100%;
            height: 200px;
            overflow: hidden;
            position: relative;
            background: #f8f9fa;
        }

        .activity-media img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .activity-media video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .activity-content {
            padding: 15px;
        }

        .activity-title {
            font-size: 1.2em;
            color: #2c3e50;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .activity-description {
            color: #666;
            font-size: 0.9em;
            margin-bottom: 10px;
        }

        .activity-date {
            color: #888;
            font-size: 0.8em;
            text-align: right;
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
            transition: all 0.3s;
        }

        .pagination .current {
            background-color: #4CAF50;
            color: white;
        }

        .pagination a:hover {
            background-color: #4CAF50;
            color: white;
        }

        .no-activities {
            text-align: center;
            padding: 40px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }

            .activities-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Arsip Kegiatan</h1>
        </div>

        <?php if (empty($activities)): ?>
            <div class="no-activities">
                <h3>Belum ada kegiatan yang diarsipkan</h3>
            </div>
        <?php else: ?>
            <div class="activities-grid">
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
                                <div class="no-media">Tidak ada media</div>
                            <?php endif; ?>
                        </div>
                        <div class="activity-content">
                            <h3 class="activity-title"><?php echo htmlspecialchars($activity['activity_name']); ?></h3>
                            <p class="activity-description"><?php echo htmlspecialchars($activity['activity_description']); ?></p>
                            <p class="activity-date">
                                Diarsipkan pada: <?php echo date('d/m/Y H:i', strtotime($activity['created_at'])); ?>
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>">&laquo; Previous</a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <?php if ($i == $page): ?>
                            <span class="current"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1; ?>">Next &raquo;</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>
