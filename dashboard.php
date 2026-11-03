
<?php
session_start();

require "config/database.php";

// Periksa login
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

// Ambil data pengguna
$nama = $_SESSION["nama"] ?? "Pengguna";
$peran = $_SESSION["peran"] ?? "pegawai";
$user_id = (int) $_SESSION["user_id"];

$kasi = $_SESSION["kasi"] ?? $_SESSION["seksi"] ?? "Belum diatur";

// Hitung jumlah notulen
if ($peran == "admin") {

    $sql = $koneksi->prepare("
        SELECT COUNT(*) AS jumlah
        FROM notulen
    ");

} else {

    $sql = $koneksi->prepare("
        SELECT COUNT(*) AS jumlah
        FROM notulen
        WHERE user_id = ?
    ");

    $sql->bind_param("i", $user_id);
}

$sql->execute();

$hasil = $sql->get_result();
$data = $hasil->fetch_assoc();

$jumlah_notulen = (int) $data["jumlah"];

$sql->close();

// Ambil daftar Kasi/seksi yang aktif
$query_kasi = mysqli_query($koneksi, "
    SELECT DISTINCT seksi
    FROM users
    WHERE seksi IS NOT NULL
    AND seksi != ''
    AND peran = 'pegawai'
    AND status = 'aktif'
    ORDER BY seksi ASC
");

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard | SIPENA Kecamatan Gantar</title>

    <link rel="stylesheet" href="asset/css/style.css">

    <style>

        /* Bagian daftar Kasi */

        .daftar-kasi {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .kartu-kasi {
            background: white;
            padding: 20px;
            border-radius: 10px;
            border-left: 5px solid #1769aa;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            text-decoration: none;
            color: #333;
            transition: 0.3s;
        }

        .kartu-kasi:hover {
            transform: translateY(-4px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.12);
        }

        .kartu-kasi h3 {
            color: #1769aa;
            margin-bottom: 10px;
        }

        .kartu-kasi p {
            color: #666;
            font-size: 14px;
        }

        .judul-bagian {
            margin-top: 30px;
            margin-bottom: 15px;
        }

        .profil-info {
            display: flex;
            flex-direction: column;
        }

        .profil-info span {
            font-size: 13px;
            color: #777;
        }

        @media screen and (max-width: 600px) {

            .daftar-kasi {
                grid-template-columns: 1fr;
            }

        }

    </style>

</head>

<body class="halaman-dashboard">

    <!-- SIDEBAR -->
    <aside class="sidebar">

        <h2>SIPENA <span>KG</span></h2>

        <p class="sidebar-label">MENU UTAMA</p>

        <a href="dashboard.php" class="menu-aktif">
            Dashboard
        </a>

        <a href="pages/isi-notulen.php">
            Isi Notulen
        </a>

        <a href="pages/daftar-notulen.php">
            Daftar Notulen
        </a>

        <a href="pages/dokumen.php">
            Dokumen Rapat
        </a>

        <a href="pages/absensi.php">
            Absensi Pegawai
        </a>

        <a href="pages/jadwal.php">
            Jadwal Rapat
        </a>

        <a href="pages/surat-resmi.php">
            Surat Rapat Resmi
        </a>

        <?php if ($peran == "admin") : ?>

            <p class="sidebar-label">ADMINISTRASI</p>

            <a href="pages/kelola-akun.php">
                Kelola Akun
            </a>

        <?php endif; ?>

        <a href="ganti-password.php">
            Ganti Password
        </a>

        <a href="logout.php" class="menu-logout">
            Keluar
        </a>

    </aside>


    <!-- KONTEN DASHBOARD -->
    <main class="konten-dashboard">

        <!-- HEADER -->
        <header class="dashboard-header">

            <div>

                <h1>Dashboard</h1>

                <p>
                    Sistem Informasi Pengelolaan Notulen
                </p>

            </div>

            <div class="profil">

                <div class="profil-info">

                    <strong>
                        <?php
                        echo htmlspecialchars($nama, ENT_QUOTES, "UTF-8");
                        ?>
                    </strong>

                    <span>
                        <?php
                        echo htmlspecialchars($peran, ENT_QUOTES, "UTF-8");
                        ?>
                    </span>

                </div>

            </div>

        </header>


        <!-- SAMBUTAN -->
        <section class="sambutan">

            <h2>Selamat Datang di SIPENA!</h2>

            <p>
                Halo,
                <strong>
                    <?php
                    echo htmlspecialchars($nama, ENT_QUOTES, "UTF-8");
                    ?>
                </strong>.

                Selamat bekerja. Silakan gunakan menu yang tersedia
                untuk mengelola notulen dan kegiatan rapat.
            </p>

            <p>
                Kasi / Bagian:

                <strong>
                    <?php
                    echo htmlspecialchars($kasi, ENT_QUOTES, "UTF-8");
                    ?>
                </strong>
            </p>

        </section>


        <!-- STATISTIK -->
        <section class="statistik">

            <div class="statistik-card">

                <p>
                    <?php
                    echo ($peran == "admin")
                        ? "Total Notulen"
                        : "Notulen Saya";
                    ?>
                </p>

                <h2>
                    <?php echo $jumlah_notulen; ?>
                </h2>

            </div>


            <div class="statistik-card">

                <p>Kasi / Bagian</p>

                <h2 class="teks-seksi">
                    <?php
                    echo htmlspecialchars($kasi, ENT_QUOTES, "UTF-8");
                    ?>
                </h2>

            </div>


            <div class="statistik-card">

                <p>Hak Akses</p>

                <h2 class="teks-seksi">
                    <?php
                    echo htmlspecialchars($peran, ENT_QUOTES, "UTF-8");
                    ?>
                </h2>

            </div>

        </section>


        <!-- DAFTAR KASI -->
        <section class="bagian-kasi">

            <h2 class="judul-bagian">
                Daftar Kasi / Bagian Kecamatan Gantar
            </h2>

            <div class="daftar-kasi">

                <?php

                if ($query_kasi && mysqli_num_rows($query_kasi) > 0) {

                    while ($kasi_data = mysqli_fetch_assoc($query_kasi)) {

                        $nama_seksi = $kasi_data["seksi"];

                ?>

                    <div class="kartu-kasi">

                        <h3>
                            <?php
                            echo htmlspecialchars(
                                $nama_seksi,
                                ENT_QUOTES,
                                "UTF-8"
                            );
                            ?>
                        </h3>

                        <p>
                            Bagian Kecamatan Gantar
                        </p>

                    </div>

                <?php

                    }

                } else {

                ?>

                    <p>
                        Belum ada data Kasi atau bagian yang aktif.
                    </p>

                <?php } ?>

            </div>

        </section>


        <!-- AKSES CEPAT -->
        <section class="aksi-cepat">

            <h2 class="judul-bagian">
                Akses Cepat
            </h2>

            <div class="aksi-container">


                <a href="pages/isi-notulen.php" class="aksi-card">

                    <span>📝</span>

                    <h3>Isi Notulen</h3>

                    <p>
                        Buat catatan rapat baru.
                    </p>

                </a>


                <a href="pages/daftar-notulen.php" class="aksi-card">

                    <span>📁</span>

                    <h3>Daftar Notulen</h3>

                    <p>
                        Lihat catatan rapat tersimpan.
                    </p>

                </a>


                <a href="pages/dokumen.php" class="aksi-card">

                    <span>📄</span>

                    <h3>Dokumen Rapat</h3>

                    <p>
                        Kelola dokumen rapat.
                    </p>

                </a>


                <a href="pages/absensi.php" class="aksi-card">

                    <span>👥</span>

                    <h3>Absensi Pegawai</h3>

                    <p>
                        Kelola kehadiran peserta.
                    </p>

                </a>


                <a href="pages/jadwal.php" class="aksi-card">

                    <span>📅</span>

                    <h3>Jadwal Rapat</h3>

                    <p>
                        Lihat jadwal kegiatan.
                    </p>

                </a>


                <a href="pages/surat-resmi.php" class="aksi-card">

                    <span>📜</span>

                    <h3>Surat Rapat Resmi</h3>

                    <p>
                        Upload, lihat, download, dan hapus surat resmi.
                    </p>

                </a>


                <?php if ($peran == "admin") : ?>

                    <a href="pages/kelola-akun.php" class="aksi-card">

                        <span>⚙️</span>

                        <h3>Kelola Akun</h3>

                        <p>
                            Mengatur akun pegawai SIPENA.
                        </p>

                    </a>

                <?php endif; ?>


            </div>

        </section>

    </main>

</body>

</html>
```