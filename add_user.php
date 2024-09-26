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

    // Panggil fungsi untuk menghubungkan ke Mikrotik
    $API = connectToMikrotik();

    // Cek apakah koneksi berhasil
    if ($API) {
        // Ambil daftar profil hotspot
        $API->write('/ip/hotspot/user/profile/print');
        $profiles = $API->read();

        // Tutup koneksi setelah data diambil
        $API->disconnect();
    } else {
        echo "Tidak dapat terhubung ke Mikrotik.";
    }
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>SB Admin - Bootstrap Admin Template</title>

    <!-- Bootstrap Core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="css/sb-admin.css" rel="stylesheet">

    <!-- Custom Fonts -->
    <link href="font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

</head>

<body>
<script>
// Jika ingin menggunakan modal alih-alih alert
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const success = urlParams.get('success');
    const fail = urlParams.get('fail');

    if (success === '1') {
        // Gunakan modal atau alert untuk menampilkan pesan
        alert('User Berhasil ditambahkan!');
    } else if (fail === '1') {
        // Gunakan modal atau alert untuk menampilkan pesan
        alert('User gagal ditambahkan!');
    }
});
</script>

    <div id="wrapper">

        <!-- Navigation -->
        <nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header">
                <img src="images/q.png" style="max-width: 13%;>
            </div>
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="index.php">Hotspot Admin</a>
            </div>
            <!-- Top Menu Items -->
            <ul class="nav navbar-right top-nav">
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-user"></i> Selamat datang, <?= $_SESSION['username']; ?> <b class="caret"></b></a>
                    <ul class="dropdown-menu">
                        <li>
                            <a href="logout.php"><i class="fa fa-fw fa-power-off"></i> Log Out</a>
                        </li>
                    </ul>
                </li>
            </ul>
            <!-- Sidebar Menu Items - These collapse to the responsive navigation menu on small screens -->
            <div class="collapse navbar-collapse navbar-ex1-collapse">
                <ul class="nav navbar-nav side-nav">
                    <li>
                        <a href="index.php"><i class="fa fa-fw fa-bar-chart-o"></i> Pengguna Aktif</a>
                    </li>
                    <li>
                        <a href="list_user.php"><i class="fa fa-fw fa-table"></i> List User</a>
                    </li>
                    <li class="active">
                        <a href="add_user.php"><i class="fa fa-fw fa-edit"></i> Tambah User</a>
                    </li>
                    <li>
                        <a href="list_user_profile.php"><i class="fa fa-fw fa-desktop"></i> List User Profil</a>
                    </li>
                    <li>
                        <a href="add_profile.php"><i class="fa fa-fw fa-wrench"></i> Tambah User Profil</a>
                    </li>
                </ul>
            </div>
            <!-- /.navbar-collapse -->
        </nav>

        <div id="page-wrapper">

            <div class="container-fluid">

                <!-- Page Heading -->
                <div class="row">
                    <div class="col-lg-12">
                        <h1 class="page-header">
                            Tambah User
                        </h1>
                        <ol class="breadcrumb">
                            <li>
                                <i class="fa fa-dashboard"></i>  <a href="add_user.php">Tambah User</a>
                            </li>
                        </ol>
                    </div>
                </div>
                <!-- /.row -->

                <div class="row">
                    <div class="col-lg-6">

                        
                        <form action="add_hotspot_user.php" method="post">
                            <div class="form-group">
                                <label>Username</label>
                                <input class="form-control" type="text" id="username" name="username" required>
                                <p class="help-block">Masukan Username Untuk Login Contoh Jonny</p>
                            </div>

                            <div class="form-group">
                                <label>Password</label>
                                <input class="form-control" type="text" id="password" name="password" required>
                                <p class="help-block">Masukan Password Dengan Kombinasi Angka Contoh 123Jonny123</p>
                            </div>

                            <div class="form-group">
                                <label>Profile</label>
                                <select class="form-control" id="profile" name="profile" required>
                                    <?php
                                    // Loop melalui profil yang didapat dari MikroTik dan buat opsi dropdown
                                    if (!empty($profiles)) {
                                        foreach ($profiles as $profile) {
                                            echo "<option value='" . $profile['name'] . "'>" . $profile['name'] . "</option>";
                                        }
                                    }
                                    ?>
                                </select>
                                <p class="help-block">Pilih Profile Untuk Mengatur Bandwith Dan Max User</p>
                            </div>
                            
                            <div>
                            <button type="submit" class="btn btn-primary" name="submit" value="Tambah User">Tambah User</button>
                            </div>
                        </form>
                </div>
                <!-- /.row -->

            </div>
            <!-- /.container-fluid -->

        </div>
        <!-- /#page-wrapper -->

    </div>
    <!-- /#wrapper -->

    <!-- jQuery -->
    <script src="js/jquery.js"></script>

    <!-- Bootstrap Core JavaScript -->
    <script src="js/bootstrap.min.js"></script>

</body>

</html>
