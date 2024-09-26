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
    $oldName = $_POST['old_name'];
    $newName = $_POST['name'];
    $sharedUsers = $_POST['shared_users'];
    $downloadRate = $_POST['download_rate'];
    $uploadRate = $_POST['upload_rate'];
    $rateLimit = $downloadRate . '/' . $uploadRate;

    // Panggil fungsi untuk menghubungkan ke Mikrotik
    $API = connectToMikrotik();

    // Cek apakah koneksi berhasil
    if ($API) {
        // Cek apakah nama profile baru sudah ada
        $API->write('/ip/hotspot/user/profile/print');
        $existingProfiles = $API->read();

        $profileExists = false;
        foreach ($existingProfiles as $profile) {
            if (isset($profile['name']) && $profile['name'] == $newName && $profile['name'] != $oldName) {
                $profileExists = true;
                break;
            }
        }

        if ($profileExists) {
            // Jika nama profile sudah ada
            header("Location: list_user_profile.php?error=exists");
            exit();
        } else {
            // Cari ID profile lama berdasarkan nama
            $API->write('/ip/hotspot/user/profile/print', false);
            $API->write('?name=' . $oldName);
            $profile = $API->read();

            if (!empty($profile) && is_array($profile)) {
                $profileId = $profile[0]['.id']; // Ambil ID profile untuk diubah
                
                // Update profile dengan nama baru
                $API->write('/ip/hotspot/user/profile/set', false);
                $API->write('=.id=' . $profileId, false);
                $API->write('=name=' . $newName, false);
                $API->write('=shared-users=' . $sharedUsers, false);
                $API->write('=rate-limit=' . $rateLimit);
                $response = $API->read();

                // Tutup koneksi ke Mikrotik
                $API->disconnect();

                // Redirect ke halaman daftar dengan pesan sukses
                header("Location: list_user_profile.php?success=1");
                exit();
            } else {
                // Jika profile tidak ditemukan
                header("Location: list_user_profile.php?error=not_found");
                exit();
            }
        }
    } else {
        echo "Tidak dapat terhubung ke Mikrotik.";
    }
}
?>
