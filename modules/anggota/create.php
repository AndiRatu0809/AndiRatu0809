<?php
// modules/anggota/create.php dan edit.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

require_once '../../config/database.php';
$database = new Database();
$db = $database->getConnection();

$edit_mode = false;
$anggota = null;

if (isset($_GET['id'])) {
    $edit_mode = true;
    $id = $_GET['id'];
    
    $query = "SELECT * FROM anggota WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":id", $id);
    $stmt->execute();
    $anggota = $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        if ($edit_mode) {
            $query = "UPDATE anggota SET 
                     nim = :nim,
                     nama_lengkap = :nama_lengkap,
                     jenis_kelamin = :jenis_kelamin,
                     tempat_lahir = :tempat_lahir,
                     tanggal_lahir = :tanggal_lahir,
                     alamat = :alamat,
                     telepon = :telepon,
                     email = :email,
                     program_studi = :program_studi,
                     angkatan = :angkatan,
                     status = :status,
                     cabang_id = :cabang_id
                     WHERE id = :id";
        } else {
            $query = "INSERT INTO anggota 
                     (nim, nama_lengkap, jenis_kelamin, tempat_lahir, tanggal_lahir, 
                      alamat, telepon, email, program_studi, angkatan, status, cabang_id) 
                     VALUES 
                     (:nim, :nama_lengkap, :jenis_kelamin, :tempat_lahir, :tanggal_lahir,
                      :alamat, :telepon, :email, :program_studi, :angkatan, :status, :cabang_id)";
        }
        
        $stmt = $db->prepare($query);
        
        $stmt->bindParam(":nim", $_POST['nim']);
        $stmt->bindParam(":nama_lengkap", $_POST['nama_lengkap']);
        $stmt->bindParam(":jenis_kelamin", $_POST['jenis_kelamin']);
        $stmt->bindParam(":tempat_lahir", $_POST['tempat_lahir']);
        $stmt->bindParam(":tanggal_lahir", $_POST['tanggal_lahir']);
        $stmt->bindParam(":alamat", $_POST['alamat']);
        $stmt->bindParam(":telepon", $_POST['telepon']);
        $stmt->bindParam(":email", $_POST['email']);
        $stmt->bindParam(":program_studi", $_POST['program_studi']);
        $stmt->bindParam(":angkatan", $_POST['angkatan']);
        $stmt->bindParam(":status", $_POST['status']);
        $stmt->bindParam(":cabang_id", $_POST['cabang_id']);
        
        if ($edit_mode) {
            $stmt->bindParam(":id", $id);
        }
        
        $stmt->execute();
        header("Location: index.php");
        exit();
        
    } catch(PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $edit_mode ? 'Edit' : 'Tambah'; ?> Anggota - Sistem Hipermawa Parepare</title>
    <link rel="stylesheet" href="../../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    <div class="container-fluid">
        <div class="row">
            <?php include '../../includes/sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><?php echo $edit_mode ? 'Edit' : 'Tambah'; ?> Anggota</h1>
                </div>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <form method="POST">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">NIM</label>
                                    <input type="text" class="form-control" name="nim" required 
                                           value="<?php echo $edit_mode ? $anggota['nim'] : ''; ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Nama Lengkap</label>
                                    <input type="text" class="form-control" name="nama_lengkap" required
                                           value="<?php echo $edit_mode ? $anggota['nama_lengkap'] : ''; ?>">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Jenis Kelamin</label>
                                    <select class="form-control" name="jenis_kelamin" required>
                                        <option value="L" <?php echo $edit_mode && $anggota['jenis_kelamin'] == 'L' ? 'selected' : ''; ?>>Laki-laki</option>
                                        <option value="P" <?php echo $edit_mode && $anggota['jenis_kelamin'] == 'P' ? 'selected' : ''; ?>>Perempuan</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Program Studi</label>
                                    <input type="text" class="form-control" name="program_studi" required
                                           value="<?php echo $edit_mode ? $anggota['program_studi'] : ''; ?>">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Tempat Lahir</label>
                                    <input type="text" class="form-control" name="tempat_lahir"
                                           value="<?php echo $edit_mode ? $anggota['tempat_lahir'] : ''; ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Tanggal Lahir</label>
                                    <input type="date" class="form-control" name="tanggal_lahir"
                                           value="<?php echo $edit_mode ? $anggota['tanggal_lahir'] : ''; ?>">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Alamat</label>
                                <textarea class="form-control" name="alamat" rows="3"><?php echo $edit_mode ? $anggota['alamat'] : ''; ?></textarea>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Telepon</label>
                                    <input type="text" class="form-control" name="telepon"
                                           value="<?php echo $edit_mode ? $anggota['telepon'] : ''; ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" name="email"
                                           value="<?php echo $edit_mode ? $anggota['email'] : ''; ?>">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Angkatan</label>
                                    <input type="number" class="form-control" name="angkatan" required
                                           value="<?php echo $edit_mode ? $anggota['angkatan'] : ''; ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Status</label>
                                    <select class="form-control" name="status" required>
                                        <option value="aktif" <?php echo $edit_mode && $anggota['status'] == 'aktif' ? 'selected' : ''; ?>>
                                        <option value="aktif" <?php echo $edit_mode && $anggota['status'] == 'aktif' ? 'selected' : ''; ?>>Aktif</option>
                                        <option value="tidak aktif" <?php echo $edit_mode && $anggota['status'] == 'tidak aktif' ? 'selected' : ''; ?>>Tidak Aktif</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Cabang</label>
                                    <select class="form-control" name="cabang_id" required>
                                        <option value="">Pilih Cabang</option>
                                        <?php
                                        $cabang_query = "SELECT * FROM cabang ORDER BY nama_cabang";
                                        $cabang_stmt = $db->prepare($cabang_query);
                                        $cabang_stmt->execute();
                                        while ($cabang = $cabang_stmt->fetch(PDO::FETCH_ASSOC)) {
                                            $selected = $edit_mode && $anggota['cabang_id'] == $cabang['id'] ? 'selected' : '';
                                            echo "<option value='".$cabang['id']."' ".$selected.">".$cabang['nama_cabang']."</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="index.php" class="btn btn-secondary me-md-2">Batal</a>
                                <button type="submit" class="btn btn-primary">
                                    <?php echo $edit_mode ? 'Update' : 'Simpan'; ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <?php include '../../includes/footer.php'; ?>
    <script src="../../assets/js/bootstrap.bundle.min.js"></script>
    <script>
    // Form validation
    document.querySelector('form').addEventListener('submit', function(e) {
        const nim = document.querySelector('input[name="nim"]').value;
        const nama = document.querySelector('input[name="nama_lengkap"]').value;
        const email = document.querySelector('input[name="email"]').value;
        
        if (nim.length < 5) {
            e.preventDefault();
            alert('NIM harus minimal 5 karakter!');
            return;
        }
        
        if (nama.length < 3) {
            e.preventDefault();
            alert('Nama lengkap harus minimal 3 karakter!');
            return;
        }
        
        if (email && !email.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
            e.preventDefault();
            alert('Format email tidak valid!');
            return;
        }
    });
    </script>
</body>
</html>