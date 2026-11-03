
<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit;
}

include "../config/database.php";

$sql = "SELECT * FROM jadwal ORDER BY tanggal ASC";
$hasil = $koneksi->query($sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Rapat SIPENA</title>

    <link rel="stylesheet" href="../asset/css/jadwal.css">
</head>
<body>

    <div class="jadwal-header">
        <h1>Jadwal Rapat</h1>
        <p>Sistem Informasi Pengelolaan Notulen Kecamatan Gantar</p>
    </div>

    <div class="konten-jadwal">

        <h2>Daftar Kegiatan Rapat</h2>

        <a href="tambah-jadwal.php" class="tombol">
            + Tambah Jadwal
        </a>

        <div class="tabel-bungkus">
            <table>
                <tr>
                    <th>No</th>
                    <th>Seksi</th>
                    <th>Kegiatan</th>
                    <th>Tanggal</th>
                    <th>Batas Notulen</th>
                    <th>Keterangan</th>
                </tr>

                <?php
                $no = 1;

                while ($jadwal = $hasil->fetch_assoc()) {
                ?>

                <tr>
                    <td><?php echo $no++; ?></td>

                    <td><?php echo htmlspecialchars($jadwal["seksi"]); ?></td>

                    <td><?php echo htmlspecialchars($jadwal["kegiatan"]); ?></td>

                    <td>
                        <?php echo date("d-m-Y H:i", strtotime($jadwal["tanggal"])); ?>
                    </td>

                    <td>
                        <?php
                        if ($jadwal["batas_notulen"] != "") {
                            echo date("d-m-Y", strtotime($jadwal["batas_notulen"]));
                        } else {
                            echo "-";
                        }
                        ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($jadwal["keterangan"] ?? ""); ?>
                    </td>
                </tr>

                <?php } ?>

            </table>
        </div>

        <a href="../dashboard.php" class="tombol-kembali">
            ← Kembali ke Dashboard
        </a>

    </div>

</body>
</html>
