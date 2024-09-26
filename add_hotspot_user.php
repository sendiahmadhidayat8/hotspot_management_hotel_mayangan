<?php
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}
?>
<?php
// Include file koneksi ke Mikrotik
require('mikrotik_connection.php');

// Mengecek apakah form sudah disubmit
if (isset($_POST['submit'])) {
    // Ambil data dari form
    $username = $_POST['username'];
    $password = $_POST['password'];
    $profile = $_POST['profile'];

    // Panggil fungsi untuk menghubungkan ke Mikrotik
    $API = connectToMikrotik();

    // Cek apakah koneksi berhasil
    if ($API) {
        // Menambahkan user hotspot
        $API->write('/ip/hotspot/user/add', false);
        $API->write('=name=' . $username, false);
        $API->write('=password=' . $password, false);
        $API->write('=profile=' . $profile);
        $response = $API->read();

        // Tutup koneksi ke Mikrotik
        $API->disconnect();

        // Redirect ke halaman add_user.php dengan parameter sukses
        header("Location: add_user.php?success=1");
        exit();
    } else {
        header("Location: add_user.php?fail=1");
    }
}
?>

<!-- Tombol Back -->
<a href="index.php">Kembali</a>
