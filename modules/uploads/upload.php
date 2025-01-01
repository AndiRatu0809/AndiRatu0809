<?php
session_start();
include '../../config/db.php';

if ($_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php?error=access_denied");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $password = $_POST['password'] ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;

    // Handle upload foto profil
    if ($_FILES['profile_pic']['name']) {
        $target_dir = "../../uploads/";
        $profile_pic = time() . "_" . basename($_FILES['profile_pic']['name']);
        move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target_dir . $profile_pic);
    }

    // Update ke database
    $stmt = $conn->prepare("UPDATE users SET fullname = :fullname, email = :email, 
                            password = COALESCE(:password, password), 
                            profile_pic = COALESCE(:profile_pic, profile_pic) WHERE id = :id");
    $stmt->bindParam(':fullname', $fullname);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':password', $password);
    $stmt->bindParam(':profile_pic', $profile_pic);
    $stmt->bindParam(':id', $_SESSION['user_id']);
    $stmt->execute();

    header("Location: dashboard.php?success=profile_updated");
    exit();
}

// Ambil data pengguna
$stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
$stmt->bindParam(':id', $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profil</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            padding: 20px;
        }
        form {
            max-width: 400px;
            margin: auto;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        input, button {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        button {
            background-color: #007bff;
            color: #fff;
            border: none;
        }
    </style>
</head>
<body>
    <form method="POST" enctype="multipart/form-data">
        <h2>Edit Profil</h2>
        <label>Nama Lengkap</label>
        <input type="text" name="fullname" value="<?= htmlspecialchars($user['fullname']) ?>" required>
        <label>Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
        <label>Password (Kosongkan jika tidak ingin diubah)</label>
        <input type="password" name="password">
        <label>Foto Profil</label>
        <input type="file" name="profile_pic">
        <button type="submit">Simpan Perubahan</button>
    </form>
</body>
</html>
