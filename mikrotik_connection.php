
<?php
// Masukkan path ke library RouterOS-API
require('routeros_api.class.php');

function connectToMikrotik() {
    // Konfigurasi koneksi ke Mikrotik
    $API = new RouterosAPI();
    $host = ''; // Ganti dengan IP Mikrotik Anda
    $username = '';    // Ganti dengan username Mikrotik Anda
    $password = ''; // Ganti dengan password Mikrotik Anda

    // Coba koneksi ke Mikrotik
    if ($API->connect($host, $username, $password)) {
        return $API; // Kembalikan objek API jika koneksi berhasil
    } else {
        return false; // Kembalikan false jika koneksi gagal
    }
}
?>
