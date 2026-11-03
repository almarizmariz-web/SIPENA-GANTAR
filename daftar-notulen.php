<?php
session_start();
require "../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit;
}

$judul = $_GET["judul"] ?? "";
$kasi = $_GET["kasi"] ?? "";
$tanggal = $_GET["tanggal"] ?? "";

if ($_SESSION["peran"] == "admin") {

    $sql = "SELECT * FROM notulen
            WHERE judul LIKE ?
            AND seksi LIKE ?
            AND tanggal LIKE ?
            ORDER BY tanggal DESC";

    $stmt = $koneksi->prepare($sql);

    $cariJudul = "%" . $judul . "%";
    $cariKasi = "%" . $kasi . "%";

    if ($tanggal == "") {
        $cariTanggal = "%";
    } else {
        $cariTanggal = $tanggal;
    }

    $stmt->bind_param(
        "sss",
        $cariJudul,
        $cariKasi,
        $cariTanggal
    );

} else {

    $id_user = $_SESSION["user_id"];

    $sql = "SELECT * FROM notulen
            WHERE user_id = ?
            AND judul LIKE ?
            AND seksi LIKE ?
            AND tanggal LIKE ?
            ORDER BY tanggal DESC";

    $stmt = $koneksi->prepare($sql);

    $cariJudul = "%" . $judul . "%";
    $cariKasi = "%" . $kasi . "%";

    if ($tanggal == "") {
        $cariTanggal = "%";
    } else {
        $cariTanggal = $tanggal;
    }

    $stmt->bind_param(
        "isss",
        $id_user,
        $cariJudul,
        $cariKasi,
        $cariTanggal
    );
}

$stmt->execute();
$hasil = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Notulen SIPENA</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f2f5fa;
            margin: 0;
            padding: 25px;
            color: #263449;
        }

        .container {
            max-width: 1200px;
            margin: auto;
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 15px #00000010;
        }

        h2 {
            color: #163b68;
            margin-top: 0;
        }

        .tombol {
            display: inline-block;
            padding: 9px 13px;
            margin: 3px;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            color: white;
            font-size: 13px;
            cursor: pointer;
            text-align: center;
        }

        .buat {
            background: #176b45;
            margin-bottom: 18px;
        }

        .lihat {
            background: #1769aa;
        }

        .edit {
            background: #b77912;
        }

        .hapus {
            background: #c0392b;
        }

        .kembali {
            background: #526477;
            margin-top: 18px;
        }

        /* BARU: Desain Kotak Filter / Form Pencarian Notulen */
        .filter-notulen {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            background: #f8fafc;
            padding: 16px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            margin-bottom: 20px;
            align-items: center;
        }

        .filter-notulen input[type="text"],
        .filter-notulen input[type="date"] {
            flex: 1;
            min-width: 180px;
            padding: 10px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 14px;
            box-sizing: border-box;
            transition: border-color 0.2s;
        }

        .filter-notulen input:focus {
            border-color: #163b68;
            outline: none;
        }

        .filter-notulen button[type="submit"] {
            padding: 10px 20px;
            background: #163b68;
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            font-size: 14px;
            transition: background 0.2s;
        }

        .filter-notulen button[type="submit"]:hover {
            background: #0f294a;
        }

        .filter-notulen .btn-reset {
            padding: 10px 20px;
            background: #94a3b8;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            font-size: 14px;
            text-align: center;
            transition: background 0.2s;
        }

        .filter-notulen .btn-reset:hover {
            background: #64748b;
        }

        .tabel {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 850px;
        }

        th, td {
            padding: 12px;
            border: 1px solid #dce3ec;
            text-align: left;
            vertical-align: middle;
        }

        th {
            background: #163b68;
            color: white;
        }

        tr:nth-child(even) {
            background: #f7f9fc;
        }

        .aksi {
            white-space: nowrap;
        }

        /* Responsif Layar untuk HP */
        @media (max-width: 600px) {
            body {
                padding: 10px;
            }

            .container {
                padding: 15px;
            }

            .filter-notulen {
                flex-direction: column;
                align-items: stretch;
            }

            .filter-notulen input[type="text"],
            .filter-notulen input[type="date"],
            .filter-notulen button,
            .filter-notulen .btn-reset {
                width: 100%;
            }
        }
    </style>
</head>

<body>
<div class="container">

    <h2>Daftar Notulen Rapat</h2>
    
    <?php if (isset($_GET["status"]) && $_GET["status"] == "hapus") { ?>
        <p style="color: green; font-weight: bold;">
            ✓ Notulen berhasil dihapus.
        </p>
    <?php } ?>

    <?php if (isset($_GET["status"]) && $_GET["status"] == "gagal") { ?>
        <p style="color: red; font-weight: bold;">
            ✗ Notulen gagal dihapus atau kamu tidak memiliki akses.
        </p>
    <?php } ?>

    <a href="isi-notulen.php" class="tombol buat">
        + Buat Notulen
    </a>

    <!-- Form Pencarian dengan Penambahan Kelas CSS -->
    <form method="GET" action="daftar-notulen.php" class="filter-notulen">
        <input
            type="text"
            name="judul"
            placeholder="Cari judul rapat..."
            value="<?= htmlspecialchars($_GET['judul'] ?? '') ?>"
        >

        <input
            type="text"
            name="kasi"
            placeholder="Cari seksi/Kasi..."
            value="<?= htmlspecialchars($_GET['kasi'] ?? '') ?>"
        >

        <input
            type="date"
            name="tanggal"
            value="<?= htmlspecialchars($_GET['tanggal'] ?? '') ?>"
        >

        <button type="submit">Cari</button>
        <a href="daftar-notulen.php" class="btn-reset">Reset</a>
    </form>

    <div class="tabel">
        <table>
            <thead>
                <tr>
                    <th style="width: 5px;">No.</th>
                    <th>Judul Rapat</th>
                    <th>Kasi</th>
                    <th>Tanggal</th>
                    <th>Pimpinan</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                
            <?php
            $nomor = 1;

            while ($row = $hasil->fetch_assoc()) {
            ?>
                <tr>
                    <td><?= $nomor ?></td>

                    <td>
                        <strong><?= htmlspecialchars($row["judul"]) ?></strong>
                    </td>

                    <td>
                        <?= htmlspecialchars($row["seksi"]) ?>
                    </td>

                    <td>
                        <?= date("d-m-Y", strtotime($row["tanggal"])) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($row["pimpinan"]) ?>
                    </td>

                    <td class="aksi">
                        <a class="tombol lihat"
                           href="detail-notulen.php?id=<?= (int) $row["id"] ?>">
                            Lihat
                        </a>

                        <a class="tombol edit"
                           href="edit-notulen.php?id=<?= (int) $row["id"] ?>">
                            Edit
                        </a>

                        <form action="../proses/hapus-notulen.php"
                              method="POST"
                              style="display:inline"
                              onsubmit="return confirm('Yakin ingin menghapus notulen ini?')">

                            <input type="hidden" name="id"
                                   value="<?= (int) $row["id"] ?>">

                            <button type="submit" class="tombol hapus">
                                Hapus
                            </button>
                        </form>
                    </td>
                </tr>
            <?php
                $nomor++;
            }

            if ($hasil->num_rows == 0) {
            ?>
                <tr>
                    <td colspan="6" style="text-align: center; color: #999; padding: 25px 0;">Belum ada data notulen yang cocok.</td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>

    <a href="../dashboard.php" class="tombol kembali">
        ← Kembali ke Dashboard
    </a>

</div>
</body>
</html>
