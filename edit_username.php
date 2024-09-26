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
$message = '';

// Ambil username dari URL
$userHotspot = isset($_GET['username']) ? $_GET['username'] : '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $newUsername = $_POST['new_username'];  // Username baru dari form

    if ($API) {
        // Cek apakah user Hotspot ada
        $API->write('/ip/hotspot/user/print', false);
        $API->write('?name=' . $userHotspot, true);
        $user = $API->read();

        if (count($user) > 0) {
            // Cek apakah username baru sudah ada
            $API->write('/ip/hotspot/user/print', false);
            $API->write('?name=' . $newUsername, true);
            $existingUser = $API->read();

            if (count($existingUser) > 0) {
                $message = "Username '{$newUsername}' sudah ada, silakan pilih username lain.";
            } else {
                // Jika username baru belum ada, lakukan update username
                $API->write('/ip/hotspot/user/set', false);
                $API->write('=.id=' . $user[0]['.id'], false);
                $API->write('=name=' . $newUsername, true);  // Update username
                $API->read();

                $message = "Username untuk user '{$userHotspot}' berhasil diubah menjadi '{$newUsername}'!";
                $userHotspot = $newUsername;  // Update variabel dengan nama baru
            }
        } else {
            $message = "User '{$userHotspot}' tidak ditemukan!";
        }

        $API->disconnect();
    } else {
        $message = "Koneksi ke MikroTik gagal!";
    }
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
                    <li class="active">
                        <a href="list_user.php"><i class="fa fa-fw fa-table"></i> List User</a>
                    </li>
                    <li>
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
                            Edit Username untuk User : <?php echo $userHotspot; ?>
                        </h1>
                        <ol class="breadcrumb">
                            <li>
                                <i class="fa fa-dashboard"></i>  <a href="list_user.php">List User</a>
                            </li>
                            <li class="active">
                                <i class="fa fa-edit"></i> Edit Username
                            </li>
                        </ol>
                    </div>
                </div>
                <!-- /.row -->

                <div class="row">
                    <div class="col-lg-6">
                        <!-- <form role="form"> -->

                            <div class="form-group">

                            <?php if ($message): ?>
                            <p><?php echo $message; ?></p>
                            <?php endif; ?>

                            <!-- Form untuk mengubah username -->
                            <form method="POST" action="">
                                <input class="form-control" placeholder="<?php echo $userHotspot; ?>" type="text" id="new_username" name="new_username" required><br>
                                <button type="submit" class="btn btn-primary">Update Username</button>
                                <button type="submit" class="btn btn-secondary" onclick="window.location.href='list_user.php'">Kembali</button>
                            </form>

                    </div>
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
