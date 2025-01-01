<?php
session_start();
include '../../config/db.php'; // Masukkan koneksi ke database

// Ambil data dari form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'register') {
    // Ambil input dari form
    $email = $_POST['email'];
    $fullname = $_POST['fullname'];
    $phone = $_POST['phone'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role'];

    // Validasi password
    if ($password !== $confirm_password) {
        $error_message = "Password dan konfirmasi password tidak cocok!";
    } else {
        // Enkripsi password sebelum disimpan
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Cek apakah email sudah terdaftar
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $email_exists = $stmt->fetchColumn();

        if ($email_exists) {
            $error_message = "Email sudah terdaftar!";
        } else {
            // Jika belum ada error, insert data ke dalam database
            try {
                $stmt = $pdo->prepare("INSERT INTO users (email, fullname, phone, password, role) 
                                       VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$email, $fullname, $phone, $hashed_password, $role]);

                // Redirect setelah registrasi berhasil
                $_SESSION['success_message'] = "Registrasi berhasil! Silakan login.";
                header("Location: ../auth/login.php");
                exit();
            } catch (PDOException $e) {
                $error_message = "Terjadi kesalahan saat menyimpan data: " . $e->getMessage();
            }
        }
    }
}
?>

<!-- Menampilkan form registrasi jika belum ada error -->
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Akun</title>
    <!-- <link rel="stylesheet" href="../../assets/css/register.min.css"> -->
    <style>
        body {
            background-color:rgb(8, 8, 78);
            background: url('../../assets/img/rumah-adat-atakkae.jpeg') no-repeat center fixed;
            background-size: cover;
            margin: 0;
            font-family: Arial, sans-serif;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
            overflow-y: auto;
        }

        .container {
            width: 100%;
            max-width: 600px;
            padding: 20px;
            box-sizing: border-box;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h3.text-center {
            margin-bottom: 30px;
            color: #000000; /* Warna hitam untuk judul "Registrasi Akun" */
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
            border-radius: 5px;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            font-weight: bold;
            color: #333;
            font-size: 14px;
        }

        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
        }

        .form-control:focus {
            border-color:rgb(30, 255, 0);
            background-color: #f9f9f9;
        }

        .btn-primary {
            background-color:rgb(61, 38, 4);
    border: none;
    padding: 12px 18px;
    font-size: 16px;
    border-radius: 8px;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #fff;
    cursor: pointer;
    width: 100%;
    transition: background-color 0.3s;
        }

        .btn-primary:hover {
            background-color:rgb(43, 182, 38);
        }

        .text-center a {
            color:rgb(30, 255, 0);
            text-decoration: none;
        }

        .text-center a:hover {
            text-decoration: underline;
        }

        /* Styling untuk logo */
        .login-logo {
            display: block;
        margin: 0 auto 20px;
        width: 100px;
        border-radius: 50%;  /* Memberikan margin di bawah dan center */
        }

        /* Menambahkan warna hitam pada teks "Sudah punya akun?" */
        .text-center p {
            color: #000; /* Warna hitam untuk tulisan "Sudah punya akun?" */
        }

        @media (max-width: 767px) {
            .container {
                padding: 15px;
            }

            .form-group label {
                font-size: 13px;
            }

            .btn-primary {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <img src="../../assets/img/logo.png" alt="Logo" class="login-logo">
            <h3 class="text-center mb-4">Registrasi Akun</h3>

            <!-- Tampilkan error jika ada -->
            <?php if (isset($error_message)): ?>
                <div class="error"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <!-- Form Registrasi -->
            <form action="register_handler.php" method="POST">
                <input type="hidden" name="action" value="register">
                <div class="form-group mb-3">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="Masukkan email" required>
                </div>
                <div class="form-group mb-3">
                    <label for="fullname">Full Name</label>
                    <input type="text" id="fullname" name="fullname" class="form-control" placeholder="Masukkan nama lengkap" required>
                </div>
                <div class="form-group mb-3">
                    <label for="phone">Phone</label>
                    <input type="text" id="phone" name="phone" class="form-control" placeholder="Masukkan nomor telepon" required>
                </div>
                <div class="form-group mb-3">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Masukkan password" required>
                </div>
                <div class="form-group mb-3">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Konfirmasi password" required>
                </div>
                <!-- Menambahkan dropdown untuk role -->
                <div class="form-group mb-3">
                    <label for="role">Role</label>
                    <select name="role" required>
                        <?php if ($show_admin_role): ?>
                            <option value="admin">Admin</option>
                        <?php endif; ?>
                        <option value="pengurus">Pengurus</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary w-100">Register</button>
            </form>

            <p class="text-center mt-3">Sudah punya akun? <a href="../auth/login.php">Login di sini</a></p>
        </div>
    </div>
</div>
</body>
</html>
