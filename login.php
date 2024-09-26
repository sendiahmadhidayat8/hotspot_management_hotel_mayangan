<?php
session_start();

// Include file koneksi
require 'db_connection.php'; // Pastikan nama file sesuai

// Cek jika user sudah login, langsung redirect ke halaman utama
if (isset($_SESSION['username'])) {
    header('Location: index.php');
    exit();
}

// Proses login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Query untuk memeriksa username
    $sql = "SELECT * FROM users WHERE username = :username"; // Menggunakan parameter
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verifikasi password
    if ($user) {
        if (password_verify($password, $user['password'])) {
            // Simpan session
            $_SESSION['username'] = $user['username']; // Simpan username asli
            
            // Cek jika user adalah super user
            $sql_super = "SELECT * FROM super_users WHERE username = :username";
            $stmt_super = $pdo->prepare($sql_super);
            $stmt_super->execute(['username' => $username]);
            $super_user = $stmt_super->fetch(PDO::FETCH_ASSOC);

            if ($super_user) {
                // Redirect ke halaman manage user jika super user
                header('Location: list_user_login.php');
            } else {
                // Redirect ke halaman utama untuk pengguna biasa
                header('Location: index.php');
            }
            exit();
        } else {
            $error = "Username atau password salah!";
        }
    } else {
        $error = "Username tidak ditemukan!";
    }
}
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
            <div class="col-4">
                <div class="card">
                    <div class="card-body">
                        <div class="login-title">
                            <img src="images/login_title.png" alt="Login" style="max-width: 100%; height: auto;">
                        </div>
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>
                        <form method="POST">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Login</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
