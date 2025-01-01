<?php
session_start();
include '../../config/db.php';

// Hanya admin yang bisa mengakses halaman ini
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Ambil semua log aktivitas
$stmt = $pdo->prepare("
    SELECT users.fullname, activity_log.activity, activity_log.activity_description, activity_log.created_at, activity_log.id AS log_id 
    FROM activity_log
    JOIN users ON activity_log.user_id = users.id
    ORDER BY activity_log.created_at DESC
");
$stmt->execute();
$activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Aktivitas Pengguna</title>
    <style>
        /* CSS */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
        }

        h2 {
            text-align: center;
            background-color: #007bff;
            color: white;
            padding: 20px;
            margin: 0;
        }

        .container {
            padding: 20px;
            max-width: 1000px;
            margin: 0 auto;
        }

        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 10px;
            text-align: center;
        }

        th {
            background-color: #007bff;
            color: white;
        }

        .action-btn {
            padding: 5px 10px;
            margin: 5px;
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }

        .action-btn:hover {
            background-color: #0056b3;
        }

        .action-btn.delete {
            background-color: #dc3545;
        }

        .action-btn.delete:hover {
            background-color: #c82333;
        }

        .action-btn.view {
            background-color: #28a745;
        }

        .action-btn.view:hover {
            background-color: #218838;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            padding-top: 60px;
        }

        .modal-content {
            background-color: #fff;
            margin: 5% auto;
            padding: 20px;
            width: 80%;
            border-radius: 8px;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
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
<a href="../dashboard/admin_dashboard.php" class="button-back">Kembali ke Dashboard</a>
    <div class="container">
        <h2>Log Aktivitas Pengguna</h2>

        <table>
            <tr>
                <th>Nama Pengguna</th>
                <th>Aktivitas</th>
                <th>Deskripsi</th>
                <th>Waktu</th>
                <th>Aksi</th>
            </tr>
            <?php foreach ($activities as $activity): ?>
            <tr>
                <td><?php echo htmlspecialchars($activity['fullname']); ?></td>
                <td><?php echo htmlspecialchars($activity['activity']); ?></td>
                <td><?php echo htmlspecialchars($activity['activity_description']); ?></td>
                <td><?php echo htmlspecialchars($activity['created_at']); ?></td>
                <td>
                    <!-- Tombol untuk melihat detail -->
                    <button class="action-btn view" onclick="openModal('<?php echo $activity['log_id']; ?>')">Lihat</button>

                    <!-- Tombol untuk menghapus log -->
                    <button class="action-btn delete" onclick="deleteLog('<?php echo $activity['log_id']; ?>')">Hapus</button>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <!-- Modal untuk melihat detail aktivitas -->
    <div id="activityModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h3>Detail Aktivitas</h3>
            <div id="activityDetail">
                <!-- Detail aktivitas akan ditampilkan di sini -->
            </div>
        </div>
    </div>

    <script>
        // Fungsi untuk membuka modal dan menampilkan detail aktivitas
        function openModal(log_id) {
            fetch('get_activity_details.php?id=' + log_id)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('activityDetail').innerHTML = `
                        <p><strong>Nama Pengguna:</strong> ${data.fullname}</p>
                        <p><strong>Aktivitas:</strong> ${data.activity}</p>
                        <p><strong>Deskripsi:</strong> ${data.activity_description}</p>
                        <p><strong>Waktu:</strong> ${data.created_at}</p>
                    `;
                    document.getElementById('activityModal').style.display = "block";
                });
        }

        // Fungsi untuk menutup modal
        function closeModal() {
            document.getElementById('activityModal').style.display = "none";
        }

        // Fungsi untuk menghapus log aktivitas
        function deleteLog(log_id) {
            if (confirm("Apakah Anda yakin ingin menghapus log ini?")) {
                window.location.href = 'delete_activity_log.php?id=' + log_id;
            }
        }
    </script>

</body>
</html>
