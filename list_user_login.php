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

    header('Location: list_user_login.php');
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

    header('Location: list_user_login.php');
    exit();
}

// Fungsi untuk menghapus pengguna
if (isset($_POST['delete_user'])) {
    $username = $_POST['username'];

    $sql = "DELETE FROM users WHERE username = :username";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['username' => $username]);

    $_SESSION['success_message'] = "User '$username' berhasil dihapus!";
    header('Location: list_user_login.php');
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
                        <h1>Manage Users</h1>
                        <!-- Tampilkan notifikasi jika ada -->
                        <?php if (isset($_SESSION['success_message'])): ?>
                            <div class="alert alert-success">
                                <?= $_SESSION['success_message'] ?>
                            </div>
                        <?php unset($_SESSION['success_message']); // Hapus notifikasi setelah ditampilkan ?>
                        <?php endif; ?>
                        <h5>Existing Users</h5>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($user['username']) ?></td>
                                        <td>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="username" value="<?= htmlspecialchars($user['username']) ?>">
                                                <input type="password" name="new_password" placeholder="New Password" required>
                                                <button type="submit" name="edit_password" class="btn btn-warning btn-sm">Edit Password</button>
                                            </form>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="username" value="<?= htmlspecialchars($user['username']) ?>">
                                                <button type="submit" name="delete_user" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this user?');">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <button class="btn btn-primary" onclick="window.location.href='add_user_login.php'">Add User</button>
                        <button class="btn btn-secondary" onclick="window.location.href='index.php'">Dashboard</button>
                        <button class="btn btn-danger" onclick="window.location.href='logout.php'">LogOut</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
