<?php
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}
?>
<?php
// Memuat koneksi dari file terpisah
require('mikrotik_connection.php');

$API = connectToMikrotik();
$response = ['success' => false, 'message' => ''];

// Ambil username dari POST
$userHotspot = isset($_POST['username']) ? $_POST['username'] : '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($API) {
        // Cek apakah user Hotspot ada
        $API->write('/ip/hotspot/user/print', false);
        $API->write('?name=' . $userHotspot, true);
        $user = $API->read();

        if (count($user) > 0) {
            // Jika user ditemukan, lakukan hapus
            $API->write('/ip/hotspot/user/remove', false);
            $API->write('=.id=' . $user[0]['.id'], true);
            $API->read();

            $response['success'] = true;
            $response['message'] = "User '{$userHotspot}' berhasil dihapus!";
        } else {
            $response['message'] = "User '{$userHotspot}' tidak ditemukan!";
        }

        $API->disconnect();
    } else {
        $response['message'] = "Koneksi ke MikroTik gagal!";
    }
}

header('Content-Type: application/json');
echo json_encode($response);
?>
