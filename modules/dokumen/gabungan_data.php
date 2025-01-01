<?php
// Koneksi ke database drive_ui
$conn_drive_ui = new mysqli("localhost", "root", "", "drive_ui");

// Cek koneksi drive_ui
if ($conn_drive_ui->connect_error) {
    die("Koneksi ke database drive_ui gagal: " . $conn_drive_ui->connect_error);
}

// Koneksi ke database hipermawa_db
$conn_hipermawa_db = new mysqli("localhost", "root", "", "hipermawa_db");

// Cek koneksi hipermawa_db
if ($conn_hipermawa_db->connect_error) {
    die("Koneksi ke database hipermawa_db gagal: " . $conn_hipermawa_db->connect_error);
}

// Ambil data dari tabel folders di database drive_ui
$folders = $conn_drive_ui->query("SELECT * FROM folders");

// Ambil data dari tabel anggota di database hipermawa_db
$anggota = $conn_hipermawa_db->query("SELECT * FROM anggota");

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Gabungan dari Dua Database</title>
</head>
<body>
    <h2>Data dari Database drive_ui (Tabel: folders)</h2>
    <table border="1" cellpadding="10" cellspacing="0">
        <tr>
            <th>ID Folder</th>
            <th>Nama Folder</th>
        </tr>
        <?php
        // Tampilkan data folders
        if ($folders->num_rows > 0) {
            while ($folder = $folders->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $folder['id'] . "</td>";
                echo "<td>" . $folder['name'] . "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='2'>Tidak ada data folder</td></tr>";
        }
        ?>
    </table>

    <h2>Data dari Database hipermawa_db (Tabel: anggota)</h2>
    <table border="1" cellpadding="10" cellspacing="0">
        <tr>
            <th>ID Anggota</th>
            <th>Nama Anggota</th>
            <th>Alamat</th>
        </tr>
        <?php
        // Tampilkan data anggota
        if ($anggota->num_rows > 0) {
            while ($member = $anggota->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $member['id'] . "</td>";
                echo "<td>" . $member['name'] . "</td>";
                echo "<td>" . $member['address'] . "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='3'>Tidak ada data anggota</td></tr>";
        }
        ?>
    </table>
</body>
</html>

<?php
// Tutup koneksi ke kedua database
$conn_drive_ui->close();
$conn_hipermawa_db->close();
?>
