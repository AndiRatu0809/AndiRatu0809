
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="../../assets/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <img src="../../assets/img/logo.png" alt="Logo" class="login-logo">
            <h3 class="text-center">Login</h3>

            <!-- Pesan sukses -->
            <?php if (isset($_GET['success']) && $_GET['success'] === 'logout'): ?>
                <div class="alert alert-success">Anda telah logout dengan sukses.</div>
            <?php endif; ?>

            <!-- Pesan error -->
            <?php if (isset($_GET['error'])): ?>
                <?php if ($_GET['error'] === 'invalid_password'): ?>
                    <div class="alert alert-danger">Password salah. Silakan coba lagi.</div>
                <?php elseif ($_GET['error'] === 'user_not_found'): ?>
                    <div class="alert alert-danger">Email tidak ditemukan. Silakan coba lagi.</div>
                <?php endif; ?>
            <?php endif; ?>

            <form action="login_handler.php" method="POST">
                <input type="hidden" name="action" value="login">
                <div class="form-group mb-3">
                    <label for="username">Email</label>
                    <input type="text" id="username" name="username" class="form-control" placeholder="Masukkan email" required>
                </div>
                <div class="form-group mb-3">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Masukkan password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Login</button>
            </form>

            <p class="text-center mt-3">Belum punya akun? <a href="register.php">Daftar di sini</a></p>
        </div>
    </div>
</div>
</body>
</html>
