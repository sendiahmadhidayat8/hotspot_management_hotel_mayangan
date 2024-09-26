<?php
session_start();

// Cek apakah pengguna sudah login dan memiliki peran super user
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'superuser') {
    header('Location: login.php'); // Ganti dengan halaman login
    exit();
}

// Include file koneksi
require 'db_connection.php';

// Ambil daftar pengguna
$sql = "SELECT * FROM users";
$stmt = $pdo->query($sql);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Tambah pengguna
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_user'])) {
    $new_username = $_POST['new_username'];
    $new_password = password_hash($_POST['new_password'], PASSWORD_BCRYPT);
    
    // Insert ke database
    $sql = "INSERT INTO users (username, password) VALUES (:username, :password)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['username' => $new_username, 'password' => $new_password]);
    header('Location: admin.php'); // Refresh halaman
    exit();
}

// Fungsi untuk mengubah password
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $username = $_POST['username'];
    $old_password = $_POST['old_password'];
    $new_password = password_hash($_POST['new_password'], PASSWORD_BCRYPT);
    
    // Ambil pengguna berdasarkan username
    $sql = "SELECT * FROM users WHERE username = :username";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Verifikasi password lama
    if ($user && password_verify($old_password, $user['password'])) {
        // Update password
        $sql = "UPDATE users SET password = :new_password WHERE username = :username";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['new_password' => $new_password, 'username' => $username]);
        $success_message = "Password berhasil diubah!";
    } else {
        $error_message = "Password lama salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Page</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1 class="mt-5">Halaman Admin</h1>

        <!-- Daftar pengguna -->
        <h2 class="mt-4">Daftar Pengguna</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= $user['id'] ?></td>
                        <td><?= $user['username'] ?></td>
                        <td>
                            <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#changePasswordModal" data-username="<?= $user['username'] ?>">Ubah Password</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Form untuk menambah pengguna -->
        <h2 class="mt-4">Tambah Pengguna</h2>
        <form method="POST">
            <div class="mb-3">
                <label for="new_username" class="form-label">Username</label>
                <input type="text" class="form-control" id="new_username" name="new_username" required>
            </div>
            <div class="mb-3">
                <label for="new_password" class="form-label">Password</label>
                <input type="password" class="form-control" id="new_password" name="new_password" required>
            </div>
            <button type="submit" name="add_user" class="btn btn-primary">Tambah Pengguna</button>
        </form>

        <!-- Modal untuk mengubah password -->
        <div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="changePasswordModalLabel">Ubah Password</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form method="POST">
                            <input type="hidden" name="username" id="modal_username">
                            <div class="mb-3">
                                <label for="old_password" class="form-label">Password Lama</label>
                                <input type="password" class="form-control" id="old_password" name="old_password" required>
                            </div>
                            <div class="mb-3">
                                <label for="new_password" class="form-label">Password Baru</label>
                                <input type="password" class="form-control" id="new_password" name="new_password" required>
                            </div>
                            <button type="submit" name="change_password" class="btn btn-success">Ubah Password</button>
                        </form>
                        <?php if (isset($success_message)): ?>
                            <div class="alert alert-success mt-3"><?= $success_message ?></div>
                        <?php endif; ?>
                        <?php if (isset($error_message)): ?>
                            <div class="alert alert-danger mt-3"><?= $error_message ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        var changePasswordModal = document.getElementById('changePasswordModal');
        changePasswordModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var username = button.getAttribute('data-username');
            var modalUsernameInput = changePasswordModal.querySelector('#modal_username');
            modalUsernameInput.value = username;
        });
    </script>
</body>
</html>
