<?php
session_start();

// Cek apakah pengguna sudah login dan memiliki role admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// modules/dashboard/index.php
require_once '../../config/database.php';
$database = new Database();
$db = $database->getConnection();

// Get total anggota
$query = "SELECT COUNT(*) as total FROM anggota";
$stmt = $db->prepare($query);
$stmt->execute();
$total_anggota = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Get total kegiatan
$query = "SELECT COUNT(*) as total FROM kegiatan";
$stmt = $db->prepare($query);
$stmt->execute();
$total_kegiatan = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Get total dokumen
$query = "SELECT COUNT(*) as total FROM dokumen";
$stmt = $db->prepare($query);
$stmt->execute();
$total_dokumen = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Get recent activities (Kegiatan Terbaru)
$query = "SELECT * FROM kegiatan ORDER BY tanggal_mulai DESC LIMIT 5";
$stmt = $db->prepare($query);
$stmt->execute();
$recent_activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistem Hipermawa Parepare</title>
    <link rel="stylesheet" href="../../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        /* Gaya untuk sidebar */
        .sidebar {
            background-color: #343a40;
            color: white;
            height: 100vh;
            position: fixed;
            width: 250px;
            padding-top: 20px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }

        .sidebar .nav-link {
            color: #ddd;
            padding: 12px 20px;
            font-size: 15px;
            transition: all 0.3s ease;
            border-radius: 5px;
            display: block;
        }

        .sidebar .nav-link:hover {
            color: white;
            background-color: #007bff;
            text-decoration: none;
        }

        /* Gaya untuk main content */
        .main-content {
            margin-left: 270px;
            padding: 30px;
            background-color: #ffffff;
            border-radius: 15px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }

        .main-content h1 {
            font-size: 24px;
            color: #333;
            margin-bottom: 20px;
        }

        /* Card Styles */
        .card {
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            transition: transform 0.3s ease-in-out;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card-body {
            padding: 20px;
        }

        .bg-primary {
            background-color: #007bff !important;
        }

        .bg-success {
            background-color: #28a745 !important;
        }

        .bg-info {
            background-color: #17a2b8 !important;
        }

        /* Gaya untuk Kegiatan Terbaru */
        .recent-activities {
            margin-top: 30px;
        }

        .recent-activities h3 {
            color: #333;
            font-size: 20px;
            margin-bottom: 15px;
        }

        .activity {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .activity-name {
            font-size: 16px;
            color: #333;
            flex-grow: 1;
        }

        .activity-date {
            font-size: 14px;
            color: #666;
        }
    </style>

</head>
<body>
    <?php include '../../includes/header.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar">
                <?php include '../../includes/sidebar.php'; ?>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <h1>Kegiatan</h1>

                <!-- Summary Cards -->
                <div class="row">
                    <div class="col-md-4 mb-4">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h5 class="card-title">Total Anggota</h5>
                                <h2 class="card-text"><?php echo $total_anggota; ?></h2>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 mb-4">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h5 class="card-title">Total Kegiatan</h5>
                                <h2 class="card-text"><?php echo $total_kegiatan; ?></h2>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 mb-4">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <h5 class="card-title">Total Dokumen</h5>
                                <h2 class="card-text"><?php echo $total_dokumen; ?></h2>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tombol untuk Tambah Kegiatan Baru -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <a href="add_kegiatan.php" class="btn btn-success">Tambah Kegiatan Baru</a>
                    </div>
                </div>

                <!-- Kegiatan Terbaru -->
                <div class="recent-activities">
                    <h3>Kegiatan Terbaru</h3>
                    <?php foreach ($recent_activities as $activity): ?>
                        <div class="activity">
                            <div class="activity-name"><?php echo htmlspecialchars($activity['nama_kegiatan']); ?></div>
                            <div class="activity-date"><?php echo htmlspecialchars($activity['tanggal_mulai']); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>

            </div>
        </div>
    </div>

    <?php include '../../includes/footer.php'; ?>

    <script src="../../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
