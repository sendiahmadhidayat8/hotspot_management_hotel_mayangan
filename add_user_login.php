<?php
session_start();
require 'db_connection.php'; // Pastikan nama file sesuai

// Cek jika user sudah login dan memiliki akses yang sesuai
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

// Cek apakah pengguna adalah super user
$sql_super = "SELECT * FROM super_users WHERE username = :username";
$stmt_super = $pdo->prepare($sql_super);
$stmt_super->execute(['username' => $_SESSION['username']]);
$super_user = $stmt_super->fetch(PDO::FETCH_ASSOC);

if (!$super_user) {
    // Jika bukan super user, redirect ke halaman utama
    header('Location: index.php');
    exit();
}

// Fungsi untuk menambahkan pengguna
if (isset($_POST['add_user'])) {
    $new_username = $_POST['new_username'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Cek apakah username sudah ada di database
    $sql_check = "SELECT * FROM users WHERE username = :username";
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->execute(['username' => $new_username]);
    $existing_user = $stmt_check->fetch(PDO::FETCH_ASSOC);

    if ($existing_user) {
        // Set notifikasi jika username sudah ada
        $_SESSION['error_message'] = "Username '$new_username' sudah ada. Mohon gunakan username lain.";
    } else {
        // Validasi jika kedua password cocok
        if ($new_password === $confirm_password) {
            // Hash password dan simpan ke database
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            $sql = "INSERT INTO users (username, password) VALUES (:username, :password)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['username' => $new_username, 'password' => $hashed_password]);

            // Set notifikasi sukses
            $_SESSION['success_message'] = "User '$new_username' berhasil ditambahkan!";
        } else {
            // Set notifikasi error jika password tidak cocok
            $_SESSION['error_message'] = "Password tidak cocok. Mohon coba lagi.";
        }
    }

    header('Location: add_user_login.php');
    exit();
}

// Fungsi untuk mengedit password pengguna
if (isset($_POST['edit_password'])) {
    $username = $_POST['username'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Validasi jika password cocok
    if ($new_password === $confirm_password) {
        // Hash password dan update di database
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        $sql = "UPDATE users SET password = :password WHERE username = :username";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['password' => $hashed_password, 'username' => $username]);

        $_SESSION['success_message'] = "Password untuk user '$username' berhasil diubah!";
    } else {
        $_SESSION['error_message'] = "Password tidak cocok. Mohon coba lagi.";
    }

    header('Location: add_user_login.php');
    exit();
}

// Fungsi untuk menghapus pengguna
if (isset($_POST['delete_user'])) {
    $username = $_POST['username'];

    $sql = "DELETE FROM users WHERE username = :username";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['username' => $username]);

    $_SESSION['success_message'] = "User '$username' berhasil dihapus!";
    header('Location: add_user_login.php');
    exit();
}

// Mengambil daftar pengguna
$sql = "SELECT * FROM users";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-image: url('images/background.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .login-box {
            background-color: rgba(255, 255, 255, 0.8);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center align-items-center" style="height: 100vh;">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                    <h1>Manage User</h1>
                    <!-- Tampilkan notifikasi jika ada -->
                    <?php if (isset($_SESSION['success_message'])): ?>
                        <div class="alert alert-success">
                            <?= $_SESSION['success_message'] ?>
                        </div>
                        <?php unset($_SESSION['success_message']); ?>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['error_message'])): ?>
                        <div class="alert alert-danger">
                            <?= $_SESSION['error_message'] ?>
                        </div>
                        <?php unset($_SESSION['error_message']); ?>
                    <?php endif; ?>
                    <!-- Form untuk menambahkan pengguna -->
                    <form method="POST" class="mb-4">
                                <h5>Add User</h5>
                                <div class="mb-3">
                                    <label for="new_username" class="form-label">Username</label>
                                    <input type="text" class="form-control" id="new_username" name="new_username" required>
                                </div>
                                <div class="mb-3">
                                    <label for="new_password" class="form-label">Password</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                                </div>
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirm Password</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                                <button type="submit" name="add_user" class="btn btn-primary">Add User</button>
                                <button type="submit" class="btn btn-secondary" onclick="window.location.href='list_user_login.php'">List User</button>
                                <button type="submit" class="btn btn-secondary" onclick="window.location.href='index.php'">Dashboard</button>
                    </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
