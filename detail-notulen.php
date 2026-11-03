<?php
session_start();
require "../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit;
}

$id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;

if ($_SESSION["peran"] == "admin") {
    $sql = "SELECT * FROM notulen WHERE id = ?";
    $stmt = $koneksi->prepare($sql);
    $stmt->bind_param("i", $id);
} else {
    $id_user = $_SESSION["user_id"];

    $sql = "SELECT * FROM notulen WHERE id = ? AND user_id = ?";
    $stmt = $koneksi->prepare($sql);
    $stmt->bind_param("ii", $id, $id_user);
}

$stmt->execute();
$hasil = $stmt->get_result();
$data = $hasil->fetch_assoc();

if (!$data) {
    die("Notulen tidak ditemukan atau kamu tidak memiliki akses.");
}

function aman($teks) {
    return htmlspecialchars($teks ?? "", ENT_QUOTES, "UTF-8");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Notulen - SIPENA</title>

    <style>
        body {
            margin: 0;
            padding: 25px;
            background: #edf1f6;
            font-family: "Times New Roman", serif;
            color: #222;
        }

        .navigasi {
            max-width: 800px;
            margin: 0 auto 18px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            font-family: Arial, sans-serif;
        }

        .tombol {
            padding: 10px 15px;
            border: 0;
            border-radius: 5px;
            color: white;
            text-decoration: none;
            cursor: pointer;
            font-size: 14px;
        }

        .kembali { background: #526477; }
        .cetak { background: #1769aa; }
        .edit { background: #b77912; }

        .dokumen {
            max-width: 800px;
            min-height: 950px;
            margin: auto;
            padding: 45px 55px;
            background: white;
            box-shadow: 0 3px 15px #00000015;
            box-sizing: border-box;
        }

        /* PERBAIKAN STYLING KOP UNTUK LOGO DAN TEKS */
        .kop {
            display: flex;
            align-items: center;
            border-bottom: 3px double #222;
            padding-bottom: 12px;
            margin-bottom: 25px;
        }

        .kop-logo {
            width: 70px; /* Ukuran lebar logo proporsional */
            height: auto;
            margin-right: 15px;
        }

        .kop-teks {
            flex: 1;
            text-align: center;
            /* Memberikan sedikit offset ke kanan agar teks benar-benar di tengah dokumen, 
               karena terdorong oleh logo di sebelah kiri */
            padding-right: 70px; 
        }

        .kop h2, .kop h3, .kop p {
            margin: 4px 0;
        }

        .kop h2 { font-size: 19px; letter-spacing: 0.5px; }
        .kop h3 { font-size: 17px; }
        .kop p { font-size: 12px; font-style: italic; font-family: Arial, sans-serif; }

        h3.judul {
            text-align: center;
            text-decoration: underline;
            margin-bottom: 5px;
        }

        .tanggal-surat {
            text-align: center;
            margin-top: 0;
        }

        .isi {
            font-size: 15px;
            line-height: 1.6;
            text-align: justify;
        }

        .informasi {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .informasi td {
            padding: 5px 4px;
            vertical-align: top;
        }

        .informasi td:first-child {
            width: 145px;
        }

        .bagian {
            font-weight: bold;
            margin-top: 20px;
            margin-bottom: 5px;
        }

        .teks {
            white-space: pre-wrap;
            overflow-wrap: anywhere;
        }

        .tanda-tangan {
            width: 250px;
            margin: 45px 0 0 auto;
            text-align: center;
            line-height: 1.5;
        }

        .ruang-tanda-tangan {
            height: 65px;
        }

        @media (max-width: 600px) {
            body { padding: 10px; }

            .dokumen {
                padding: 25px 18px;
                min-height: auto;
            }

            .informasi td:first-child {
                width: 105px;
            }

            .kop-teks {
                padding-right: 0; /* Hapus offset di layar kecil agar flex menyesuaikan */
            }
        }

        @media print {
            body {
                padding: 0;
                background: white;
            }

            .navigasi {
                display: none !important;
            }

            .dokumen {
                max-width: none;
                min-height: auto;
                padding: 0;
                box-shadow: none;
            }

            @page {
                size: A4;
                margin: 2cm;
            }
            @media print {
    /* 1. Menghilangkan margin bawaan browser agar info header & footer terpotong otomatis */
    @page {
        margin: 0;
    }
    
    /* 2. Memberikan margin internal pada body agar teks isi tidak terlalu mepet ke ujung kertas */
    body {
        margin: 1.5cm;
    }
}

        }
    </style>
</head>

<body>

<div class="navigasi">
    <a href="daftar-notulen.php" class="tombol kembali">← Kembali</a>
    <a href="edit-notulen.php?id=<?= (int) $data["id"] ?>" class="tombol edit">Edit Notulen</a>
    <button onclick="window.print()" class="tombol cetak">Cetak Notulen</button>
</div>

<div class="dokumen">

    <!-- PERUBAHAN STRUKTUR KOP DENGAN LOGO DAN ALAMAT JALAN -->
    <div class="kop">
        <!-- Pastikan file logo-indramayu.png sudah diletakkan di folder ../assets/img/ atau sesuaikan path-nya -->
        <img src="../img/download (1).jpg" alt="Logo Indramayu" class="kop-logo">
        
        <div class="kop-teks">
            <h2>PEMERINTAH KABUPATEN INDRAMAYU</h2>
            <h3>KECAMATAN GANTAR</h3>
            <p>Jl. Raya Gantar No. 1, Kecamatan Gantar, Kabupaten Indramayu, Kode Pos 45264</p>
        </div>
    </div>

    <h3 class="judul">NOTULEN RAPAT</h3>
    <p class="tanggal-surat">
        <?= aman($data["judul"]) ?>
    </p>

    <div class="isi">
        <table class="informasi">
            <tr>
                <td>Hari/Tanggal</td>
                <td>: <?= date("d-m-Y", strtotime($data["tanggal"])) ?></td>
            </tr>
            <tr>
                <td>Waktu</td>
                <td>: <?= aman($data["waktu"] ?? "Belum diisi") ?></td>
            </tr>
            <tr>
                <td>Tempat</td>
                <td>: <?= aman($data["tempat"] ?? "Belum diisi") ?></td>
            </tr>
            <tr>
                <td>Kasi</td>
                <td>: <?= aman($data["seksi"]) ?></td>
            </tr>
            <tr>
                <td>Pimpinan Rapat</td>
                <td>: <?= aman($data["pimpinan"]) ?></td>
            </tr>
            <tr>
                <td>Peserta</td>
                <td>: <span class="teks"><?= aman($data["peserta"] ?? "Belum diisi") ?></span></td>
            </tr>
        </table>

        <p class="bagian">I. Agenda Rapat</p>
        <div class="teks"><?= aman($data["agenda"]) ?></div>

        <p class="bagian">II. Pembahasan</p>
        <div class="teks"><?= aman($data["pembahasan"]) ?></div>

        <p class="bagian">III. Hasil Keputusan</p>
        <div class="teks"><?= aman($data["keputusan"]) ?></div>

        <p class="bagian">IV. Tindak Lanjut</p>
        <div class="teks"><?= aman($data["tindak_lanjut"] ?? "Belum diisi") ?></div>

        <p style="margin-top: 25px;">
            Demikian notulen rapat ini dibuat sebagai dokumentasi dan bahan tindak lanjut kegiatan.
        </p>

        <div class="tanda-tangan">
            <p>Gantar, <?= date("d-m-Y", strtotime($data["tanggal"])) ?></p>
            <p>Notulis,</p>

            <?php if (!empty($data["tanda_tangan"])) { ?>
                <?php
                $namaFile = basename($data["tanda_tangan"]);
                $lokasi = "../uploads/tanda-tangan/" . $namaFile;
                ?>
                <?php if (is_file($lokasi)) { ?>
                    <img src="<?= aman($lokasi) ?>" alt="Tanda tangan notulis" style="max-width:150px; max-height:70px;">
                <?php } else { ?>
                    <div class="ruang-tanda-tangan"></div>
                <?php } ?>
            <?php } else { ?>
                <div class="ruang-tanda-tangan"></div>
            <?php } ?>

            <p>
                <strong><u><?= aman($data["notulis_nama"] ?? "") ?></u></strong><br>
                NIP. <?= aman($data["notulis_nip"] ?? "") ?>
            </p>
        </div>
    </div>
</div>

</body>
</html>
