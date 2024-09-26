<?php
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}
?>
<?php
require('mikrotik_connection.php');

$API = connectToMikrotik();
if (!$API) {
    echo "Koneksi ke Mikrotik gagal.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $sharedUsers = $_POST['shared-users'] ?? '1'; // Default ke 1 jika kosong
    $downloadRate = $_POST['download-rate'] ?? '';
    $uploadRate = $_POST['upload-rate'] ?? '';

    // Membangun rate-limit dengan format "download/upload"
    $rateLimit = '';
    if ($downloadRate !== '' && $uploadRate !== '') {
        $rateLimit = $downloadRate . 'M/' . $uploadRate . 'M';
    }

    // Membangun array untuk data profil baru
    $data = [
        'name' => $name,
        'shared-users' => $sharedUsers // Pastikan shared-users selalu ada
    ];

    // Tambahkan data rate-limit jika tidak kosong
    if ($rateLimit !== '') {
        $data['rate-limit'] = $rateLimit;
    }

    // Tambah profil baru
    $API->comm('/ip/hotspot/user/profile/add', $data);

    // Redirect setelah penambahan
    header('Location: list_user_profile.php');
    exit;
}

// Tutup koneksi
$API->disconnect();
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
                    <li>
                        <a href="list_user.php"><i class="fa fa-fw fa-table"></i> List User</a>
                    </li>
                    <li>
                        <a href="add_user.php"><i class="fa fa-fw fa-edit"></i> Tambah User</a>
                    </li>
                    <li>
                        <a href="list_user_profile.php"><i class="fa fa-fw fa-desktop"></i> List User Profil</a>
                    </li>
                    <li class="active">
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
                            Tambah Profile
                        </h1>
                        <ol class="breadcrumb">
                            <li>
                                <i class="fa fa-dashboard"></i>  <a href="add_profile.php">Tambah Profile</a>
                            </li>
                        </ol>
                    </div>
                </div>
                <!-- /.row -->

                <div class="row">
                    <div class="col-lg-6">

                        
                        <form action="" method="post">
                            <div class="form-group">
                                <label>Profile</label>
                                <input class="form-control" type="text" id="name" name="name" required>
                                <p class="help-block">Masukan Profile Contoh Tamu</p>
                            </div>

                            <div class="form-group">
                                <label>Share User</label>
                                <input class="form-control" type="number" id="shared-users" name="shared-users">
                                <p class="help-block">Masukan Jumlah Share User (Kosongkan Untuk Tanpa Batas)</p>
                            </div>

                            <div class="form-group">
                                <label>Rate Limit Download (M)</label>
                                <input class="form-control" type="number" id="download-rate" name="download-rate" value=4>
                                <p class="help-block">Masukan Rate Limit Download Contoh 10 (kosongkan jika tidak dibatas)</p>
                            </div>

                            <div class="form-group">
                                <label>Rate Limit Upload (M)</label>
                                <input class="form-control" type="number" id="upload-rate" name="upload-rate" value=4>
                                <p class="help-block">Masukan Rate Limit Upload Contoh 10 (kosongkan jika tidak dibatas)</p>
                            </div>
                            
                            <div>
                            <button class="btn btn-primary" type="submit" value="Tambah">Tambah Profile</button>
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
