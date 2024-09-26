<?php
// Password yang ingin di-hash
$password = '123'; // Ganti dengan password yang diinginkan

// Menghasilkan hash dari password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Menampilkan hasil hash
echo "Password asli: " . $password . "<br>";
echo "Hash Password: " . $hashedPassword . "<br>";
?>
