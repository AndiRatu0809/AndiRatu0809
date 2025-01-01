<?php
// recent_activity.php (Pengurus dan Admin Dashboard)
session_start();
include dirname(__DIR__, 2) . '/config/db.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../dashboard_pengurus/login.php");
    exit();
}

// Function to validate file upload
function validateFileUpload($file) {
    $allowed_image_types = ['image/jpeg', 'image/png', 'image/gif'];
    $allowed_video_types = ['video/mp4', 'video/webm', 'video/ogg'];
    $max_file_size = 10 * 1024 * 1024; // 10MB

    if ($file['size'] > $max_file_size) {
        return "File terlalu besar. Maksimal ukuran file adalah 10MB.";
    }

    if (!in_array($file['type'], array_merge($allowed_image_types, $allowed_video_types))) {
        return "Tipe file tidak didukung. Gunakan file gambar (JPG, PNG, GIF) atau video (MP4, WEBM, OGG).";
    }

    return true;
}

// Handle file upload
function handleFileUpload($file) {
    $upload_dir = '../../uploads/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $new_filename = uniqid() . '.' . $file_extension;
    $upload_path = $upload_dir . $new_filename;

    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        return '/uploads/' . $new_filename;
    }
    return false;
}

// Handle activity submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_activity'])) {
    try {
        $activity_name = filter_var($_POST['activity_name'], FILTER_SANITIZE_STRING);
        $activity_description = filter_var($_POST['activity_description'], FILTER_SANITIZE_STRING);
        $media_url = null;

        if (!empty($_FILES['media']['name'])) {
            $validation_result = validateFileUpload($_FILES['media']);
            if ($validation_result === true) {
                $media_url = handleFileUpload($_FILES['media']);
                if (!$media_url) {
                    throw new Exception("Gagal mengunggah file media.");
                }
            } else {
                throw new Exception($validation_result);
            }
        }

        $stmt = $pdo->prepare("
            INSERT INTO activities (activity_name, activity_description, media_url, created_by) 
            VALUES (:name, :description, :media_url, :created_by)
        ");

        $stmt->execute([
            ':name' => $activity_name,
            ':description' => $activity_description,
            ':media_url' => $media_url,
            ':created_by' => $_SESSION['user_id']
        ]);

        $_SESSION['message'] = "Kegiatan berhasil ditambahkan.";
        header("Location: recent_activity.php");
        exit();

    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
}

// Pagination configuration
$records_per_page = 12;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $records_per_page;

try {
    // Count total activities for the current user and admin
    $count_stmt = $pdo->prepare("SELECT COUNT(*) FROM activities WHERE created_by = :user_id OR :user_role = 'admin'");
    $count_stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $count_stmt->bindValue(':user_role', $_SESSION['role'], PDO::PARAM_STR);
    $count_stmt->execute();
    $total_activities = $count_stmt->fetchColumn();
    $total_pages = ceil($total_activities / $records_per_page);

    // Get activities for the current user or all activities if admin
    $stmt = $pdo->prepare("
        SELECT * FROM activities 
        WHERE created_by = :user_id OR :user_role = 'admin'
        ORDER BY created_at DESC 
        LIMIT :limit OFFSET :offset
    ");
    
    $stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->bindValue(':user_role', $_SESSION['role'], PDO::PARAM_STR);
    $stmt->bindValue(':limit', $records_per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    die("Terjadi kesalahan dalam mengakses database.");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Kegiatan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f4f4f4;
        margin: 0;
        padding: 0;
    }

    .container {
        width: 90%;
        max-width: 1200px;
        margin: auto;
        padding: 20px;
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-bottom: 20px;
    }

    .header h1 {
        margin: 0;
        color: #333;
    }

    .btn-add {
        text-decoration: none;
        background-color: #28a745;
        color: white;
        padding: 10px 15px;
        border-radius: 5px;
        transition: background-color 0.3s ease;
    }

    .btn-add:hover {
        background-color: #218838;
    }

    .alert {
        padding: 10px;
        margin-bottom: 20px;
        border-radius: 5px;
    }

    .alert-success {
        background-color: #d4edda;
        color: #155724;
    }

    .alert-error {
        background-color: #f8d7da;
        color: #721c24;
    }

    .no-activities {
        text-align: center;
        margin-top: 50px;
    }

    .activities-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
    }

    .activity-card {
        background-color: #f9f9f9;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 1px 5px rgba(0, 0, 0, 0.1);
    }

    .activity-media img,
    .activity-media video {
        width: 100%;
        height: auto;
    }

    .activity-content {
        padding: 15px;
    }

    .activity-title {
        font-size: 1.2em;
        color: #333;
    }

    .activity-description {
        color: #666;
    }

    .activity-meta {
        margin-top: 10px;
        font-size: 0.9em;
        color: #999;
    }

    .pagination {
        display: flex;
        justify-content: center;
        margin-top: 20px;
    }

    .pagination a,
    .pagination span.current {
        margin-right: 5px;
        padding: 5px 10px;
        border-radius: 3px;
        text-decoration: none; 
    }

    .pagination a {
      background-color:#007bff; 
      color:white; 
      transition:.3s; 
      border:none; 
      cursor:pointer; 
   }
   
   .pagination a:hover { 
      background-color:#0056b3; 
   }
   
   .pagination span.current { 
      background-color:#6c757d; 
      color:white; 
   }
</style>

</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Daftar Kegiatan Saya</h1>
            <a href="add_activity.php" class="btn-add">
                <i class="fas fa-plus"></i> Tambah Kegiatan Baru
            </a>
        </div>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success">
                <?php 
                echo $_SESSION['message']; 
                unset($_SESSION['message']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?php 
                echo $_SESSION['error']; 
                unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (empty($activities)): ?>
            <div class="no-activities">
                <h3>Belum ada kegiatan yang ditambahkan</h3>
                <p>Mulai tambahkan kegiatan pertama Anda</p>
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
                                <div class="no-media">
                                    <i class="fas fa-image fa-2x"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="activity-content">
                            <h3 class="activity-title"><?php echo htmlspecialchars($activity['activity_name']); ?></h3>
                            <p class="activity-description"><?php echo htmlspecialchars($activity['activity_description']); ?></p>
                            <div class="activity-meta">
                                <p class="activity-date">
                                    <i class="far fa-calendar"></i>
                                    <?php echo date('d/m/Y H:i', strtotime($activity['created_at'])); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>">
                            <i class="fas fa-chevron-left"></i> Previous
                        </a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <?php if ($i == $page): ?>
                            <span class="current"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1; ?>">
                            Next <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <script>
        // Add fade effect to alerts
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.transition = 'opacity 0.5s ease-in-out';
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 500);
                }, 3000);
            });
        });
    </script>
</body>
</html>
