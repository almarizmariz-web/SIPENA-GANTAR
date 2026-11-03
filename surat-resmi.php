<?php
session_start();

require_once "../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit;
}

if (empty($_SESSION["csrf_surat"])) {
    $_SESSION["csrf_surat"] = bin2hex(random_bytes(32));
}

$user_id = $_SESSION["user_id"];
$peran = $_SESSION["peran"] ?? "";

if ($peran == "admin") {
    $query = mysqli_query($koneksi, "
        SELECT surat_resmi.*, users.nama
        FROM surat_resmi
        JOIN users ON surat_resmi.user_id = users.id
        ORDER BY surat_resmi.id DESC
    ");
} else {
    $query = mysqli_query($koneksi, "
        SELECT surat_resmi.*, users.nama
        FROM surat_resmi
        JOIN users ON surat_resmi.user_id = users.id
        WHERE surat_resmi.user_id = '$user_id'
        ORDER BY surat_resmi.id DESC
    ");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Rapat Resmi - SIPENA</title>
    <link rel="stylesheet" href="../asset/css/surat-resmi.css">

    <style>
        .surat-container {
            padding: 25px;
        }

        .surat-box {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
        }

        .surat-box input {
            display: block;
            margin: 15px 0;
        }

        .btn-surat {
            padding: 9px 14px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            cursor: pointer;
            display: inline-block;
            margin: 3px;
            color: white;
        }

        .btn-upload { background: #1769aa; }
        .btn-lihat { background: #16834a; }
        .btn-download { background: #1769aa; }
        .btn-hapus { background: #c0392b; }

        .tabel-surat {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        .tabel-surat th,
        .tabel-surat td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }

        .tabel-surat th {
            background: #1769aa;
            color: white;
        }

        .pesan {
            background: #e8f5e9;
            padding: 12px;
            margin-bottom: 15px;
            border-radius: 5px;
        }

        .tabel-scroll {
            overflow-x: auto;
        }
    </style>
</head>

<body>

<div class="surat-container">

    <h2>📜 Surat Rapat Resmi</h2>
    <p>Upload dan kelola surat rapat resmi Kecamatan Gantar.</p>

    <?php if (isset($_GET["status"])) { ?>
        <p class="pesan">
            <?php
            if ($_GET["status"] == "berhasil") {
                echo "Surat berhasil diupload.";
            } elseif ($_GET["status"] == "dihapus") {
                echo "Surat berhasil dihapus.";
            } elseif ($_GET["status"] == "gagal") {
                echo "Terjadi kesalahan. Silakan coba lagi.";
            }
            ?>
        </p>
    <?php } ?>

    <div class="surat-box">
        <h3>Upload Surat Baru</h3>

        <form action="../proses/tambah-surat-resmi.php"
              method="POST"
              enctype="multipart/form-data">

            <input type="hidden" name="csrf_surat"
                   value="<?php echo htmlspecialchars($_SESSION["csrf_surat"]); ?>">

            <label>Pilih file surat (PDF):</label>
            <input type="file" name="file_surat"
                   accept=".pdf,application/pdf" required>

            <button type="submit" class="btn-surat btn-upload">
                Upload Surat
            </button>

        </form>
    </div>

    <h3>Daftar Surat</h3>

    <div class="tabel-scroll">
        <table class="tabel-surat">
            <tr>
                <th>No.</th>
                <th>Nama File</th>
                <th>Tanggal Upload</th>
                <?php if ($peran == "admin") { ?>
                    <th>Pengunggah</th>
                <?php } ?>
                <th>Aksi</th>
            </tr>

            <?php
            $no = 1;

            if ($query && mysqli_num_rows($query) > 0) {
                while ($surat = mysqli_fetch_assoc($query)) {
            ?>

            <tr>
                <td><?php echo $no++; ?></td>

                <td>
                    <?php echo htmlspecialchars($surat["nama_file"]); ?>
                </td>

                <td>
                    <?php echo date("d-m-Y H:i", strtotime($surat["tanggal_upload"])); ?>
                </td>

                <?php if ($peran == "admin") { ?>
                    <td><?php echo htmlspecialchars($surat["nama"]); ?></td>
                <?php } ?>

                <td>
                    <a class="btn-surat btn-lihat"
                       target="_blank"
                       href="../proses/unduh-surat-resmi.php?id=<?php echo $surat["id"]; ?>&mode=lihat">
                       Lihat
                    </a>

                    <a class="btn-surat btn-download"
                       href="../proses/unduh-surat-resmi.php?id=<?php echo $surat["id"]; ?>&mode=download">
                       Download
                    </a>

                    <form action="../proses/hapus-surat-resmi.php"
                          method="POST"
                          style="display:inline;"
                          onsubmit="return confirm('Yakin ingin menghapus surat ini?');">

                        <input type="hidden" name="id"
                               value="<?php echo $surat["id"]; ?>">

                        <input type="hidden" name="csrf_surat"
                               value="<?php echo htmlspecialchars($_SESSION["csrf_surat"]); ?>">

                        <button type="submit" class="btn-surat btn-hapus">
                            Hapus
                        </button>
                    </form>
                </td>
            </tr>

            <?php
                }
            } else {
            ?>

            <tr>
                <td colspan="<?php echo ($peran == 'admin') ? 5 : 4; ?>">
                    Belum ada surat yang diupload.
                </td>
            </tr>

            <?php } ?>

        </table>
    </div>

    <br>
    <a href="../dashboard.php" style="color: #17365d; font-weight: bold; text-decoration: none;">← Kembali ke Dashboard</a>

</div>

</body>
</html>