<?php
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

// Memuat koneksi dari file terpisah
require('mikrotik_connection.php');

$API = connectToMikrotik();
$users = [];

if ($API) {
    // Ambil daftar user Hotspot
    $API->write('/ip/hotspot/user/print');
    $users = $API->read();
    $API->disconnect();
} else {
    echo "Koneksi ke MikroTik gagal!";
}

// Filter berdasarkan pencarian username
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = strtolower($_GET['search']);
    $users = array_filter($users, function($user) use ($search) {
        return strpos(strtolower($user['name']), $search) !== false;
    });
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

    <script>
        function confirmDelete(username) {
            return confirm('Apakah Anda yakin ingin menghapus user "' + username + '"?');
        }

        function deleteUser(username, row) {
            if (confirmDelete(username)) {
                var xhr = new XMLHttpRequest();
                xhr.open('POST', 'delete_user.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onreadystatechange = function() {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        var response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            // Hapus baris dari tabel
                            row.remove();
                        } else {
                            alert(response.message);
                        }
                    }
                };
                xhr.send('username=' + encodeURIComponent(username));
            }
        }
    </script>

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
                            List User
                        </h1>
                        <ol class="breadcrumb">
                            <li>
                                <i class="fa fa-dashboard"></i>  <a href="list_user.php">List User</a>
                            </li>
                    </div>
                </div>
                <!-- /.row -->

                <div class="row">
                    <div class="col-lg-12">
                        <div class="table-responsive">
                        <div class="text-end">
                        <form method="GET" action="list_user.php" class="form-inline">
                            <div class="form-group">
                                <input type="text" class="form-control" id="search" name="search" placeholder="Cari Username">
                            </div>
                            <button type="submit" class="btn btn-primary">Cari</button>
                            <br></br>
                        </form>
    <table class="table table-bordered table-hover table-striped">
        <thead>
            <tr>
                <th>Username</th>
                <th>Password</th>
                <th>Profil</th> <!-- Kolom baru untuk Profil -->
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['name']); ?></td>
                    <td><?php echo isset($user['password']) ? htmlspecialchars($user['password']) : 'Tidak ada'; ?></td>
                    <td>
                        <!-- Tampilkan profil user di sini -->
                        <?php echo isset($user['profile']) ? htmlspecialchars($user['profile']) : 'Tidak ada profil'; ?>
                    </td>
                    <td>
                        <!-- Link ke halaman edit_username.php dengan parameter username -->
                        <button type="submit" class="btn btn-primary" onclick="window.location.href='edit_username.php?username=<?php echo urlencode($user['name']); ?>'">Edit Username</button>
                        <button type="submit" class="btn btn-warning" onclick="window.location.href='edit_password.php?username=<?php echo urlencode($user['name']); ?>'">Edit Password</button>
                        <button class="btn btn-danger" onclick="deleteUser('<?php echo urlencode($user['name']); ?>', this.parentNode.parentNode);">Hapus</button>
                    </td>
                </tr>
            <?php endforeach; ?>
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
