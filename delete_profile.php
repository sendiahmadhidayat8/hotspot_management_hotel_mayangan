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

// Ambil nama profile dari parameter URL
$profileName = $_GET['name'];

// Panggil fungsi untuk menghubungkan ke Mikrotik
$API = connectToMikrotik();

// Cek apakah koneksi berhasil
if ($API) {
    // Cari ID profile berdasarkan nama
    $API->write('/ip/hotspot/user/profile/print', false);
    $API->write('?name=' . $profileName);
    $profile = $API->read();

    if (!empty($profile) && is_array($profile)) {
        $profileId = $profile[0]['.id']; // Ambil ID profile untuk dihapus

        // Hapus profile
        $API->write('/ip/hotspot/user/profile/remove', false);
        $API->write('=.id=' . $profileId);
        $API->read(); // Membaca response dari Mikrotik

        // Tutup koneksi ke Mikrotik
        $API->disconnect();

        // Redirect kembali ke daftar profile
        header("Location: list_user_profile.php");
        exit();
    } else {
        // Jika profile tidak ditemukan, redirect ke daftar profile
        header("Location: list_user_profile.php");
        exit();
    }
} else {
    echo "Tidak dapat terhubung ke Mikrotik.";
    exit();
}
