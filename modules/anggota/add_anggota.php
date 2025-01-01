<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Pastikan file db.php dipanggil dengan benar
require_once '../../config/db.php';  // Memanggil koneksi PDO

// Cek jika ada ID untuk edit
$anggota_id = isset($_GET['id']) ? $_GET['id'] : null;
$nah = $nama_lengkap = $alamat = $program_studi = $angkatan = $status = '';

// Jika editing, ambil data anggota
if ($anggota_id) {
    $query = "SELECT * FROM anggota WHERE id = :id LIMIT 1";
    $stmt = $pdo->prepare($query);  // Menggunakan $pdo, bukan $db
    $stmt->bindParam(':id', $anggota_id);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $nah = $row['nah'];
        $nama_lengkap = $row['nama_lengkap'];
        $alamat = $row['alamat'];
        $program_studi = $row['program_studi'];
        $angkatan = $row['angkatan'];
        $status = $row['status'];
    }
}

// Proses form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nah = $_POST['nah'];
    $nama_lengkap = $_POST['nama_lengkap'];
    $alamat = $_POST['alamat'];
    $program_studi = $_POST['program_studi'];
    $angkatan = $_POST['angkatan'];
    $status = $_POST['status'];

    if ($anggota_id) {
        // Update anggota
        $query = "UPDATE anggota SET nah = :nah, nama_lengkap = :nama_lengkap, alamat = :alamat, program_studi = :program_studi, angkatan = :angkatan, status = :status WHERE id = :id";
        $stmt = $pdo->prepare($query);  // Menggunakan $pdo, bukan $db
        $stmt->bindParam(':id', $anggota_id);
    } else {
        // Insert anggota baru
        $query = "INSERT INTO anggota (nah, nama_lengkap, alamat, program_studi, angkatan, status) 
                  VALUES (:nah, :nama_lengkap, :alamat, :program_studi, :angkatan, :status)";
        $stmt = $pdo->prepare($query);  // Menggunakan $pdo, bukan $db
    }

    // Bind params dan eksekusi
    $stmt->bindParam(':nah', $nah);
    $stmt->bindParam(':nama_lengkap', $nama_lengkap);
    $stmt->bindParam(':alamat', $alamat);
    $stmt->bindParam(':program_studi', $program_studi);
    $stmt->bindParam(':angkatan', $angkatan);
    $stmt->bindParam(':status', $status);

    if ($stmt->execute()) {
        header("Location: edit_delete_user.php");
        exit();
    } else {
        echo "Gagal menambahkan atau mengupdate data anggota.";
    }
}

// Menghapus anggota
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];

    // Pastikan ID valid sebelum melanjutkan
    if (!is_numeric($delete_id)) {
        // Jika ID tidak valid, arahkan kembali atau tampilkan error
        header("Location: edit_delete_user.php");
        exit();
    }

    // Hapus anggota
    $delete_stmt = $pdo->prepare("DELETE FROM anggota WHERE id = ?");
    $delete_stmt->execute([$delete_id]);

    // Redirect setelah menghapus anggota
    header("Location: edit_delete_user.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Anggota</title>
    <link rel="stylesheet" href="../../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
        }

        .container {
            margin-top: 30px;
        }

        h1 {
            font-size: 2.5rem;
            text-align: center;
            color: #007bff;
            margin-bottom: 40px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th, table td {
            padding: 12px;
            text-align: left;
            border: 1px solid #ddd;
        }

        table th {
            background-color: #007bff;
            color: white;
        }

        table tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        table tr:hover {
            background-color: #e9ecef;
        }

        .actions a {
            margin-right: 10px;
            color: #007bff;
            text-decoration: none;
            font-size: 14px;
        }

        .actions a:hover {
            color: #0056b3;
        }

        .btn-add {
            background-color: #28a745;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            font-size: 16px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .btn-add:hover {
            background-color: #218838;
        }

        .confirmation-dialog {
            color: #d9534f;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            table th, table td {
                font-size: 12px;
                padding: 8px;
            }

            h1 {
                font-size: 2rem;
            }
        }

    </style>
</head>
<body>

    <div class="container">
        <h1>Daftar Anggota</h1>

        <a href="tambah_anggota.php" class="btn-add">Tambah Anggota</a>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>NAH</th>
                    <th>Nama Lengkap</th>
                    <th>Jenis Kelamin</th>
                    <th>Tempat Lahir</th>
                    <th>Tanggal Lahir</th>
                    <th>Alamat</th>
                    <th>Telepon</th>
                    <th>Email</th>
                    <th>Program Studi</th>
                    <th>Angkatan</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Menampilkan semua anggota
                $stmt = $pdo->prepare("SELECT * FROM anggota");
                $stmt->execute();
                $anggota = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($anggota as $user) {
                    echo "<tr>
                            <td>" . htmlspecialchars($user['id']) . "</td>
                            <td>" . htmlspecialchars($user['nah']) . "</td>
                            <td>" . htmlspecialchars($user['nama_lengkap']) . "</td>
                            <td>" . htmlspecialchars($user['jenis_kelamin']) . "</td>
                            <td>" . htmlspecialchars($user['tempat_lahir']) . "</td>
                            <td>" . htmlspecialchars($user['tanggal_lahir']) . "</td>
                            <td>" . htmlspecialchars($user['alamat']) . "</td>
                            <td>" . htmlspecialchars($user['telepon']) . "</td>
                            <td>" . htmlspecialchars($user['email']) . "</td>
                            <td>" . htmlspecialchars($user['program_studi']) . "</td>
                            <td>" . htmlspecialchars($user['angkatan']) . "</td>
                            <td>" . htmlspecialchars($user['status']) . "</td>
                            <td class='actions'>
                                <a href='edit_delete_user.php?id=" . $user['id'] . "'>Edit</a> |
                                <a href='edit_delete_user.php?delete_id=" . $user['id'] . "' onclick='return confirm(\"Apakah Anda yakin ingin menghapus anggota ini?\")'>Hapus</a>
                            </td>
                          </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

</body>
</html>
