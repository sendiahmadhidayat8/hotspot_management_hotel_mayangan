<?php
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

// Include file koneksi ke Mikrotik
require('mikrotik_connection.php');

// Fungsi untuk mendapatkan daftar pengguna aktif berdasarkan username
function getActiveUsers($search = '') {
    $API = connectToMikrotik();
    $users = [];

    if ($API) {
        // Ambil daftar pengguna aktif
        $API->write('/ip/hotspot/active/print');
        $users = $API->read();
        $API->disconnect();
    }

    // Filter hasil berdasarkan username jika pencarian tidak kosong
    if (!empty($search)) {
        $users = array_filter($users, function($user) use ($search) {
            return stripos($user['user'], $search) !== false;
        });
    }

    return $users;
}

// Fungsi untuk menghapus pengguna aktif berdasarkan mac-address
function removeActiveUser($macAddress) {
    $API = connectToMikrotik();

    if ($API) {
        // Hapus pengguna aktif berdasarkan mac-address
        $API->write('/ip/hotspot/active/remove', false);
        $API->write("=numbers=" . $macAddress);
        $API->read();
        $API->disconnect();
    }
}

// Cek jika ada permintaan penghapusan pengguna
if (isset($_POST['mac-address'])) {
    $macAddress = $_POST['mac-address'];
    removeActiveUser($macAddress);

    // Redirect setelah penghapusan agar daftar diperbarui
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

// Ambil query pencarian dari form
$search = $_GET['search'] ?? '';

// Ambil data pengguna aktif berdasarkan pencarian
$users = getActiveUsers($search);
$activeUserCount = count($users); // Hitung jumlah pengguna aktif
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
                            <a href="edit_password_login.php"><i class="fa fa-fw fa-gear"></i> Edit Password</a>
                        </li>
                        <li>
                            <a href="logout.php"><i class="fa fa-fw fa-power-off"></i> Log Out</a>
                        </li>
                    </ul>
                </li>
            </ul>
            <!-- Sidebar Menu Items - These collapse to the responsive navigation menu on small screens -->
            <div class="collapse navbar-collapse navbar-ex1-collapse">
                <ul class="nav navbar-nav side-nav">
                    <li class="active">
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
                            Pengguna Aktif
                        </h1>
                        <ol class="breadcrumb">
                            <li>
                                <i class="fa fa-dashboard"></i>  <a href="index.php">Pengguna Aktif</a>
                            </li>
                    </div>
                </div>
                <!-- /.row -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="table-responsive">
                        <div class="text-end">
                        <form method="GET" action="index.php" class="form-inline">
                            <div class="form-group">
                                <input type="text" name="search" id="search" class="form-control" placeholder="Cari Username" value="<?= htmlspecialchars($search); ?>">
                            </div>
                            <button type="submit" class="btn btn-primary">Cari</button>
                            <br></br>
                        </form>
                    </div>
    <table class="table table-bordered table-hover table-striped">
        <thead>
            <tr>
                <th>Username</th>
                <th>Address</th>
                <th>Uptime</th>
                <th>MAC Address</th>
                <th>Login By</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (!empty($users)) {
                foreach ($users as $user) {
                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($user['user'] ?? 'N/A') . '</td>';
                    echo '<td>' . htmlspecialchars($user['address'] ?? 'N/A') . '</td>';
                    echo '<td>' . htmlspecialchars($user['uptime'] ?? 'N/A') . '</td>';
                    echo '<td>' . htmlspecialchars($user['mac-address'] ?? 'N/A') . '</td>';
                    echo '<td>' . htmlspecialchars($user['login-by'] ?? 'N/A') . '</td>';
                    echo '<td>';
                    echo '<form method="POST" action="">';
                    echo '<input type="hidden" name="mac-address" value="' . htmlspecialchars($user['.id']) . '">';
                    echo '<button type="submit" class="btn btn-danger">Kick</button>';
                    echo '</form>';
                    echo '</td>';
                    echo '</tr>';
                }
            } else {
                echo '<tr><td colspan="6">Tidak ada pengguna aktif.</td></tr>';
            }
            ?>
        </tbody>
    </table>
    <p>Jumlah Pengguna Aktif: <?php echo $activeUserCount; ?></p> <!-- Menampilkan jumlah pengguna aktif -->

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
