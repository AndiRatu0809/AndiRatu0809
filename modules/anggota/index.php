<?php
session_start();
require_once '../../config/database.php';
$database = new Database();
$db = $database->getConnection();

$query = "SELECT * FROM anggota";
$stmt = $db->prepare($query);
$stmt->execute();
$anggota = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Anggota - Sistem Hipermawa Parepare</title>
    <link rel="stylesheet" href="../../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/css/styles.css"> 
    <style>
        /* Global Body Styling */
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
            line-height: 1.6;
            color: #333;
        }

        /* Nav Flex Column Styling */
        .nav.flex-column {
            background-color: #007bff;  /* Biru background */
            padding: 10px;
            border-radius: 8px;
            box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .nav-link {
            color: white;
            text-decoration: none;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 10px;
            display: block;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        .nav-link:hover {
            background-color: #0056b3;
        }

        .nav-link.active {
            background-color: #0056b3;
            color: white;
        }

        /* Table Styling */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table th, table td {
            padding: 12px 15px;
            text-align: left;
            border: 1px solid #ddd;
        }

        table th {
            background-color: #007bff;
            color: white;
        }

        table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        table tr:hover {
            background-color: #f1f1f1;
            cursor: pointer;
        }

        /* Status Styles */
        .status {
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: bold;
            text-transform: capitalize;
        }

        .status-new { background-color: #f2f2f2; color: #333; }
        .status-open { background-color: #007bff; color: #fff; }
        .status-in-progress { background-color: #ffc107; color: #333; }
        .status-resolved { background-color: #28a745; color: #fff; }
        .status-closed { background-color: #6c757d; color: #fff; }

        /* Button Styling */
        .btn { display: inline-block; padding: 6px 12px; border: none; border-radius: 4px; font-size: 14px; cursor: pointer; }
        .btn:hover { transform: scale(1.05); }
        .btn-primary { background-color: #007bff; color: white; }
        .btn-primary:hover { background-color: #0056b3; }
        .btn-sm { padding: 4px 8px; font-size: 12px; }
        .btn-warning { background-color: #ffc107; color: #333; }
        .btn-warning:hover { background-color: #e0a800; }
        .btn-danger { background-color: #dc3545; color: #fff; }
        .btn-danger:hover { background-color: #c82333; }

        /* Responsive Design */
        @media (max-width: 768px) {
            .table th, .table td { padding: 8px; }
            .sidebar { display: none; }
            .main-content { margin-left: 0; }
        }
    </style>
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    <div class="container-fluid">
        <div class="row">
            <!-- Navigation Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar">
                <div class="nav flex-column">
                    <a href="../dashboard/index.php" class="nav-link"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                    <a href="../anggota/index.php" class="nav-link"><i class="fas fa-users"></i> Anggota</a>
                    <a href="../kegiatan/index.php" class="nav-link"><i class="fas fa-calendar-alt"></i> Kegiatan</a>
                    <a href="../dokumen/index.php" class="nav-link"><i class="fas fa-file-alt"></i> Dokumen</a>
                    <a href="../deskripsi/index.php" class="nav-link"><i class="fas fa-info-circle"></i> Deskripsi</a>
                </div>
            </div>
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <h1>Daftar Anggota</h1>
                <a href="add_anggota.php" class="btn btn-primary mb-3">Tambah Anggota</a>
                
                <!-- Tabel Anggota -->
                <table class="table">
                    <thead>
                        <tr>
                            <th>NAH</th>
                            <th>Nama</th>
                            <th>Alamat</th>
                            <th>Program Studi</th>
                            <th>Angkatan</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($anggota as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['nah']); ?></td>
                                <td><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                                <td><?php echo htmlspecialchars($row['alamat']); ?></td>
                                <td><?php echo htmlspecialchars($row['program_studi']); ?></td>
                                <td><?php echo htmlspecialchars($row['angkatan']); ?></td>
                                <td>
                                    <span class="status status-<?php echo strtolower(str_replace(' ', '-', $row['status'])); ?>">
                                        <?php echo htmlspecialchars($row['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="edit_anggota.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                    <a href="delete_anggota.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus anggota ini?')">Hapus</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php include '../../includes/footer.php'; ?>
</body>
</html>
