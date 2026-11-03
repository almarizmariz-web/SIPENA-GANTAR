<?php
session_start();
require "../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit;
}

$id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;
$user_id = (int) $_SESSION["user_id"];
$peran = $_SESSION["peran"] ?? "pegawai";

if ($peran == "admin") {
    $sql = $koneksi->prepare(
        "SELECT * FROM notulen WHERE id = ?"
    );
    $sql->bind_param("i", $id);
} else {
    $sql = $koneksi->prepare(
        "SELECT * FROM notulen WHERE id = ? AND user_id = ?"
    );
    $sql->bind_param("ii", $id, $user_id);
}

$sql->execute();
$hasil = $sql->get_result();
$data = $hasil->fetch_assoc();

$sql->close();
$koneksi->close();

if (!$data) {
    http_response_code(404);
    exit("Notulen tidak ditemukan atau kamu tidak memiliki akses.");
}

function aman($teks) {
    return htmlspecialchars($teks ?? "", ENT_QUOTES, "UTF-8");
}

function daftarIsi($teks) {
    $baris = preg_split("/\r\n|\r|\n/", trim($teks));

    echo "<ol>";

    foreach ($baris as $isi) {
        if (trim($isi) != "") {
            echo "<li>" . aman($isi) . "</li>";
        }
    }

    echo "</ol>";
}

$tanggal = date(
    "d-m-Y",
    strtotime($data["tanggal"])
);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Notulen - <?= aman($data["judul"]) ?></title>

    <style>
        @page {
            size: A4;
            margin: 2cm 2cm 2cm 2.5cm;
        }

        body {
            font-family: "Times New Roman", serif;
            font-size: 12pt;
            line-height: 1.45;
            color: #000;
            background: #eee;
            margin: 0;
        }

        .kertas {
            width: 210mm;
            min-height: 297mm;
            box-sizing: border-box;
            padding: 20mm 20mm 20mm 25mm;
            margin: 20px auto;
            background: white;
        }

        .kop {
            display: flex;
            align-items: center;
            border-bottom: 3px double black;
            padding-bottom: 10px;
            margin-bottom: 25px;
        }

        .logo {
            width: 75px;
            max-height: 85px;
            object-fit: contain;
            margin-right: 15px;
        }

        .kop-teks {
            flex: 1;
            text-align: center;
        }

        .kop-teks h2,
        .kop-teks h1,
        .kop-teks p {
            margin: 2px 0;
        }

        .kop-teks h2 {
            font-size: 14pt;
        }

        .kop-teks h1 {
            font-size: 18pt;
        }

        .judul-surat {
            text-align: center;
            margin: 25px 0;
        }

        .judul-surat h2 {
            margin-bottom: 5px;
        }

        .identitas {
            width: 100%;
            margin-bottom: 22px;
        }

        .identitas td {
            padding: 2px 0;
            vertical-align: top;
        }

        .identitas td:first-child {
            width: 130px;
        }

        .isi {
            text-align: justify;
        }

        .bagian {
            margin-top: 18px;
        }

        .bagian h3 {
            margin-bottom: 5px;
        }

        .bagian ol {
            margin-top: 5px;
            padding-left: 28px;
        }

        .bagian li {
            padding-left: 5px;
            margin-bottom: 4px;
        }

        .penutup {
            margin-top: 25px;
        }

        .tanggal-surat {
            text-align: right;
            margin-top: 25px;
        }

        .ttd {
            width: 260px;
            margin-left: auto;
            margin-top: 20px;
            text-align: center;
            page-break-inside: avoid;
        }

        .gambar-ttd {
            height: 65px;
            max-width: 180px;
            object-fit: contain;
            margin: 8px auto;
            display: block;
        }

        .nama-ttd {
            font-weight: bold;
            text-decoration: underline;
        }

        .tombol {
            display: block;
            width: 180px;
            margin: 20px auto;
            padding: 12px;
            background: #17365d;
            color: white;
            text-align: center;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        @media print {
            body {
                background: white;
            }

            .kertas {
                width: auto;
                min-height: 0;
                margin: 0;
                padding: 0;
                box-shadow: none;
            }

            .tombol {
                display: none;
            }
        }

        @media screen and (max-width: 800px) {
            .kertas {
                width: 100%;
                min-height: 0;
                margin: 0;
                padding: 20px;
            }
        }
    </style>
</head>

<body>
<div class="tombol-navigasi">
    <button class="tombol tombol-back" onclick="history.back()">
        ← Kembali
    </button>

    <button class="tombol tombol-cetak" onclick="window.print()">
        Cetak / Simpan PDF
    </button>
</div>

<div class="kertas">

    <div class="kop">

        <img
            src="../img/download%20%281%29.jpg"
            alt="Logo Kecamatan"
            class="logo"
        >

        <div class="kop-teks">
            <h2>PEMERINTAH KABUPATEN INDRAMAYU</h2>
            <h1>KECAMATAN GANTAR</h1>
            <p>Alamat Kantor Kecamatan Gantar</p>
            <p>Kabupaten Indramayu</p>
        </div>

    </div>

    <div class="judul-surat">
        <h2>NOTULEN RAPAT</h2>
        <strong><?= aman($data["judul"]) ?></strong>
    </div>

    <table class="identitas">

        <tr>
            <td>Hari / Tanggal</td>
            <td>: <?= aman(date("l", strtotime($data["tanggal"]))) ?>, <?= aman($tanggal) ?></td>
        </tr>

        <tr>
            <td>Waktu</td>
            <td>: <?= aman($data["waktu"]) ?></td>
        </tr>

        <tr>
            <td>Tempat</td>
            <td>: <?= aman($data["tempat"]) ?></td>
        </tr>

        <tr>
            <td>Pimpinan Rapat</td>
            <td>: <?= aman($data["pimpinan"]) ?></td>
        </tr>

        <tr>
            <td>Peserta</td>
            <td>: <?= nl2br(aman($data["peserta"])) ?></td>
        </tr>

        <tr>
            <td>Agenda</td>
            <td>: <?= nl2br(aman($data["agenda"])) ?></td>
        </tr>

    </table>

    <div class="isi">

        <div class="bagian">
            <h3>I. Pembahasan</h3>
            <?php daftarIsi($data["pembahasan"]); ?>
        </div>

        <div class="bagian">
            <h3>II. Hasil Keputusan</h3>
            <?php daftarIsi($data["keputusan"]); ?>
        </div>

        <div class="bagian">
            <h3>III. Tindak Lanjut</h3>
            <?php daftarIsi($data["tindak_lanjut"]); ?>
        </div>

    </div>

    <p class="penutup">
        Demikian notulen rapat ini dibuat untuk digunakan
        sebagaimana mestinya.
    </p>

    <div class="tanggal-surat">
        Gantar, <?= aman($tanggal) ?>
    </div>

    <div class="ttd">
        Notulis,

        <?php
        $file_ttd = $data["tanda_tangan"] ?? "";
        $lokasi_ttd = "../uploads/tanda-tangan/" . basename($file_ttd);

        if (
            $file_ttd != "" &&
            is_file($lokasi_ttd)
        ) :
        ?>
            <img
                src="<?= aman($lokasi_ttd) ?>"
                class="gambar-ttd"
                alt="Tanda tangan notulis"
            >
        <?php else : ?>
            <div style="height: 75px;"></div>
        <?php endif; ?>

        <div class="nama-ttd">
            <?= aman($data["notulis_nama"]) ?>
        </div>

        <div>NIP. <?= aman($data["notulis_nip"]) ?></div>
    </div>

</div>

</body>
</html>