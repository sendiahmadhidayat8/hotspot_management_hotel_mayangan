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

// Ambil nama profile dari parameter URL
$profileName = isset($_GET['name']) ? $_GET['name'] : '';

// Panggil fungsi untuk menghubungkan ke Mikrotik
$API = connectToMikrotik();

// Cek apakah koneksi berhasil
if ($API) {
    // Ambil semua profil
    $API->write('/ip/hotspot/user/profile/print');
    $profiles = $API->read();

    // Filter profil berdasarkan nama jika ada
    $profileFound = false;
    foreach ($profiles as $profile) {
        if (isset($profile['name']) && $profile['name'] == $profileName) {
            $profileFound = $profile;
            break;
        }
    }

    // Cek apakah profile ditemukan
    if ($profileFound) {
        $profile = $profileFound;
    } else {
        echo "Profile tidak ditemukan.";
        exit();
    }

    // Tutup koneksi setelah data diambil
    $API->disconnect();
} else {
    echo "Tidak dapat terhubung ke Mikrotik.";
    exit();
}

// Pisahkan rate limit menjadi download dan upload
$rateLimit = isset($profile['rate-limit']) ? $profile['rate-limit'] : '';
$rateLimitParts = explode('/', $rateLimit);
$downloadRate = isset($rateLimitParts[0]) ? $rateLimitParts[0] : '';
$uploadRate = isset($rateLimitParts[1]) ? $rateLimitParts[1] : '';
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
                        <a href="list_user_profile.php"><i class="fa fa-fw fa-bar-chart-o"></i> Pengguna Aktif</a>
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
                            Edit User Profil
                        </h1>
                        <ol class="breadcrumb">
                            <li>
                                <i class="fa fa-dashboard"></i>  <a href="list_user_profile.php">List User Profil</a>
                            </li>
                            <li class="active">
                                <i class="fa fa-edit"></i> Edit User Profil
                            </li>
                        </ol>
                    </div>
                </div>
                <!-- /.row -->

                <div class="row">
                    <div class="col-lg-6">
                        <!-- <form role="form"> -->
                        <form action="update_profile.php" method="post">
                            <input class="form-control" type="hidden" name="old_name" value="<?php echo htmlspecialchars($profile['name']); ?>">
                            <label for="name">Nama Profile</label>
                            <input class="form-control" type="text" id="name" name="name" value="<?php echo htmlspecialchars($profile['name']); ?>" required>
                            <p class="help-block">Masukan Nama Profile Contoh Tamu</p>
                            <label for="shared_users">Shared Users</label>
                            <input class="form-control" type="number" id="shared_users" name="shared_users" value="<?php echo htmlspecialchars($profile['shared-users']); ?>">
                            <p class="help-block">Masukan Jumlah Share User</p>
                            <label for="download_rate">Rate Limit Download</label>
                            <input class="form-control" type="text" id="download_rate" name="download_rate" value="<?php echo htmlspecialchars($downloadRate); ?>">
                            <p class="help-block">Masukan Rate Limit Download (Kosongkan Untuk Tanpa Batas)</p>
                            <label for="upload_rate">Rate Limit Upload</label>
                            <input class="form-control" type="text" id="upload_rate" name="upload_rate" value="<?php echo htmlspecialchars($uploadRate); ?>">
                            <p class="help-block">Masukan Rate Limit Upload (Kosongkan Untuk Tanpa Batas)</p>
                            <button type="submit" class="btn btn-primary" name="submit" value="Update Profile">Update Profile</button>
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
