<?php
include '../../config/db.php';

// Admin View Uploaded Files
$result = $pdo->query("SELECT d.*, u.fullname FROM dokumen d JOIN users u ON d.diupload_oleh = u.id");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Dokumen</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
    font-family: Arial, sans-serif;
    background-color: #f8f9fa;
    margin: 0;
    padding: 0;
}
.container {
    max-width: 800px;
    margin: 50px auto;
    background: #ffffff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}
.table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}
.table th, .table td {
    border: 1px solid #ddd;
    padding: 8px;
    text-align: left;
}
.table th {
    background-color: #007bff;
    color: #ffffff;
}
.form-group {
    margin-bottom: 15px;
}
.form-control {
    width: 100%;
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 4px;
}
.btn {
    padding: 10px 15px;
    background-color: #007bff;
    color: #ffffff;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}
.btn:hover {
    background-color: #0056b3;
}
.alert {
    padding: 10px;
    margin-bottom: 15px;
    border-radius: 4px;
}
.alert-success {
    background-color: #d4edda;
    color: #155724;
}
.alert-danger {
    background-color: #f8d7da;
    color: #721c24;
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
<a href="../dashboard/admin_dashboard.php" class="button-back">Kembali ke Dashboard</a>
    <h2>Daftar Dokumen Terupload</h2>
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nama Dokumen</th>
                <th>Deskripsi</th>
                <th>Lihat File</th>
                <th>Diupload Oleh</th>
                <th>Tanggal Upload</th>
                <!-- <th>Sudah Diverifikasi</th> -->
            </tr>
        </thead>
        <tbody>
    <?php while ($row = $result->fetch(PDO::FETCH_ASSOC)): ?>
        <tr>
            <td><?= htmlspecialchars($row['id']) ?></td>
            <td><?= htmlspecialchars($row['nama']) ?></td>
            <td><?= htmlspecialchars($row['deskripsi']) ?></td>
            <td><a href="<?= htmlspecialchars($row['lokasi_file']) ?>" target="_blank">Download</a></td>
            <td><?= htmlspecialchars($row['fullname']) ?></td>
            <td><?= htmlspecialchars($row['tanggal_upload']) ?></td>
            <!-- <td><?= $row['sudah_diverifikasi'] ? 'Ya' : 'Tidak' ?></td> -->
        </tr>
    <?php endwhile; ?>
</tbody>
</table>

</div>
</body>
</html>
