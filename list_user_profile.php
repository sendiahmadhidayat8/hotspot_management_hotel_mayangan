<?php
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
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
        exit();
    }
    ?>
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
                    <li>
                        <a href="add_user.php"><i class="fa fa-fw fa-edit"></i> Tambah User</a>
                    </li>
                    <li class="active">
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
                            List User Profil
                        </h1>
                        <ol class="breadcrumb">
                            <li>
                                <i class="fa fa-dashboard"></i>  <a href="list_user_profile.php">List User Profil</a>
                            </li>
                    </div>
                </div>
                <!-- /.row -->

                <div class="row">
                    <div class="col-lg-12">
                        <div class="table-responsive">

    <table class="table table-bordered table-hover table-striped">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Profile</th>
                <th>Shared Users</th>
                <th>Rate Limit Download</th>
                <th>Rate Limit Upload</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Periksa apakah ada profile yang didapat
            if (!empty($profiles)) {
                $no = 1;
                foreach ($profiles as $profile) {
                    // Memisahkan rate-limit menjadi download dan upload
                    if (isset($profile['rate-limit'])) {
                        $rateLimit = explode("/", $profile['rate-limit']);
                        $download = isset($rateLimit[0]) ? $rateLimit[0] : '-';
                        $upload = isset($rateLimit[1]) ? $rateLimit[1] : '-';
                    } else {
                        $download = 'unlimited';
                        $upload = 'unlimited';
                    }

                    // Mengambil flag dari profile jika ada
                    $flags = isset($profile['flags']) ? $profile['flags'] : '';

                    echo "<tr>";
                    echo "<td>" . $no . "</td>";
                    echo "<td>" . htmlspecialchars($profile['name']) . "</td>";
                    echo "<td>" . htmlspecialchars($profile['shared-users']) . "</td>";
                    echo "<td>" . htmlspecialchars($download) . "</td>";
                    echo "<td>" . htmlspecialchars($upload) . "</td>";
                    echo "<td>";
                    echo "<a href='edit_profile.php?name=" . urlencode($profile['name']) . "'>Edit</a> | ";
                    echo "<a href='delete_profile.php?name=" . urlencode($profile['name']) . "' onclick='return confirm(\"Anda yakin ingin menghapus profile ini?\");'>Hapus</a>";
                    echo "</td>";
                    echo "</tr>";
                    $no++;
                }
            } else {
                echo "<tr><td colspan='6'>Tidak ada profile ditemukan.</td></tr>";
            }
            ?>
        </tbody>
    </table>
                        </div>
                    </div>
                </div>

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
