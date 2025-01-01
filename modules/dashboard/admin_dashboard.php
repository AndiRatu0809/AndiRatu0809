
<?php
session_start();
include '../../config/db.php';

// Mengecek apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Ambil data pengguna yang sedang login
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT fullname, email, role, profile_picture FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Periksa apakah data pengguna ditemukan
if (!$user) {
    echo "Pengguna tidak ditemukan.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
    font-family: 'Arial', sans-serif;
    margin: 0;
    padding: 0;
    width: 100%;
    height: 100vh; /* Menjadikan tinggi body sesuai tinggi layar */
    background:  url('../../assets/img/rumah-adat-atakkae_169.jpeg') no-repeat center center fixed;
    background-size: cover; /* Mengisi latar belakang sepenuhnya */
    color: #333;
}


        .container {
            display: flex;
        }

        .sidebar {
            width: 300px;
            height: 100vh;
            background: #1c1c2d;
            color: #fff;
            position: fixed;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding-top: 20px;
        }

        .logo {
            text-align: center;
            margin-bottom: 20px;
        }

        .logo img {
            width: 120px;
            border-radius: 50%;
            border: 3px solid #fff;
        }

        .logo h3 {
            margin-top: 10px;
            font-size: 20px;
            text-transform: uppercase;
            letter-spacing: 2px;
            color:rgba(243, 226, 226, 0.4);
        }

        .sidebar ul {
            list-style-type: none;
            width: 100%;
            padding: 20px 0;
        }

        .sidebar ul li {
            text-align: center;
            margin: 10px 0;
        }

        .sidebar ul li a {
            text-decoration: none;
            color: #fff;
            font-size: 16px;
            padding: 15px;
            display: block;
            border-radius: 8px;
            transition: background 0.3s ease;
        }

        .sidebar ul li a:hover {
            background: #6a11cb;
        }

        .main-content {
            margin-left: 300px;
            padding: 20px;
            width: calc(100% - 300px);
        }

        .header {
            background: rgba(238, 236,236,0.65);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
        }

        .header h1 {
            font-size: 28px;
            color: #333;
        }

        .card {
            background:rgba(238, 236, 236, 0.65);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
            margin-bottom: 20px;
        }

        .card img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            margin-bottom: 15px;
            border: 3px solid #6a11cb;
        }

        .card h2 {
            font-size: 22px;
            margin-bottom: 10px;
        }

        .card p {
            font-size: 16px;
            color: #555;
        }

        .logout {
            text-align: center;
            margin-top: 20px;
        }

        .logout a {
            text-decoration: none;
            background: #dc3545;
            color: #fff;
            padding: 12px 20px;
            border-radius: 5px;
            font-size: 16px;
            transition: background 0.3s ease;
        }

        .logout a:hover {
            background:rgb(156, 25, 38);
        }

        /* Modal Styles */
        #editProfileModal {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 300px;
            z-index: 1000;
        }

        #editProfileModal h3 {
            margin-bottom: 20px;
        }

        #editProfileModal form div {
            margin-bottom: 10px;
        }

        #editProfileModal form input {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        #editProfileModal button {
            padding: 10px 20px;
            background: #6a11cb;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        #editProfileModal .cancel {
            background:rgb(119, 30, 39);
            margin-left: 10px;
        }

        #modalBackdrop {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(7, 36, 90, 0.5);
            z-index: 999;
        }
    </style>
</head>
<body>
    <div id="modalBackdrop"></div>
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="logo">
                <img src="../../uploads/<?php echo htmlspecialchars($user['profile_picture'] ?? 'default.png'); ?>" alt="Foto Profil">
                <h3><?php echo htmlspecialchars($user['role']); ?></h3>
            </div>
            <ul>
                <li><a href="admin_dashboard.php">Beranda</a></li>
                <li><a href="view_activity.php">Pertinjau Aktivitas</a></li>
                <li><a href="view_activity_log.php">Pertinjau Aktivitas Log</a></li>
                <li><a href=" ../anggota/edit_delete_pengurus.php">Edit dan Hapus Anggota</a></li>
                <li><a href="view_documents.php">Pertinjau Dokumen</a></li>
                <!-- <li><a href="view_activities.php">Pertinjau Kegiatan</a></li> -->
                <li><a href="../auth/logout.php">Logout</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1>Selamat Datang, <?php echo htmlspecialchars($user['fullname']); ?>!</h1>
            </div>

            <!-- Beranda -->
            <div class="card">
                <img src="../../uploads/<?php echo htmlspecialchars($user['profile_picture'] ?? 'default.png'); ?>" alt="Foto Profil">
                <h2><?php echo htmlspecialchars($user['fullname']); ?></h2>
                <p>Peran: <?php echo htmlspecialchars($user['role']); ?></p>
                <button id="editProfileButton" style="margin-top: 10px;">Edit Profil</button>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div id="editProfileModal">
        <h3>Edit Profil</h3>
        <form method="POST" action="update_profile.php" enctype="multipart/form-data">
            <div>
                <label for="fullname">Nama Lengkap:</label>
                <input type="text" id="fullname" name="fullname" value="<?php echo htmlspecialchars($user['fullname']); ?>">
            </div>
            <div>
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>">
            </div>
            <div>
                <label for="profile_picture">Foto Profil:</label>
                <input type="file" id="profile_picture" name="profile_picture">
            </div>
            <button type="submit">Simpan</button>
            <button type="button" class="cancel">Batal</button>
        </form>
    </div>

    <script>
        const editProfileButton = document.getElementById('editProfileButton');
        const editProfileModal = document.getElementById('editProfileModal');
        const modalBackdrop = document.getElementById('modalBackdrop');
        const cancelButtons = document.querySelectorAll('.cancel');

        editProfileButton.addEventListener('click', () => {
            modalBackdrop.style.display = 'block';
            editProfileModal.style.display = 'block';
        });

        cancelButtons.forEach(btn => btn.addEventListener('click', () => {
            modalBackdrop.style.display = 'none';
            editProfileModal.style.display = 'none';
        }));

        modalBackdrop.addEventListener('click', () => {
            modalBackdrop.style.display = 'none';
            editProfileModal.style.display = 'none';
        });
    </script>
</body>
</html>