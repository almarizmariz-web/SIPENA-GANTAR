<?php
session_start();
require "../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit;
}

$pesan = "";

if (isset($_POST["simpan"])) {

    $user_id = (int) $_SESSION["user_id"];
    $kasi = $_SESSION["kasi"] ?? "Belum diatur";

    $judul = trim($_POST["judul"]);
    $tanggal = $_POST["tanggal"];
    $waktu = trim($_POST["waktu"]);
    $tempat = trim($_POST["tempat"]);
    $pimpinan = trim($_POST["pimpinan"]);
    $peserta = trim($_POST["peserta"]);
    $agenda = trim($_POST["agenda"]);
    $pembahasan = trim($_POST["pembahasan"]);
    $keputusan = trim($_POST["keputusan"]);
    $tindak_lanjut = trim($_POST["tindak_lanjut"]);
    $notulis_nama = trim($_POST["notulis_nama"]);
    $notulis_nip = trim($_POST["notulis_nip"]);

    $nama_tanda_tangan = null;

    // Periksa tanda tangan jika diunggah
    if (
        isset($_FILES["tanda_tangan"]) &&
        $_FILES["tanda_tangan"]["error"] != UPLOAD_ERR_NO_FILE
    ) {
        if ($_FILES["tanda_tangan"]["error"] != UPLOAD_ERR_OK) {
            $pesan = "Gagal mengunggah tanda tangan.";
        } elseif ($_FILES["tanda_tangan"]["size"] > 2 * 1024 * 1024) {
            $pesan = "Ukuran tanda tangan maksimal 2 MB.";
        } else {
            $info = getimagesize($_FILES["tanda_tangan"]["tmp_name"]);

            if ($info === false) {
                $pesan = "File tanda tangan harus berupa gambar.";
            } else {
                $tipe = $info["mime"];

                if ($tipe == "image/png") {
                    $ekstensi = "png";
                } elseif ($tipe == "image/jpeg") {
                    $ekstensi = "jpg";
                } else {
                    $pesan = "Gunakan gambar PNG atau JPG.";
                }

                if ($pesan == "") {
                    $folder = "../uploads/tanda-tangan/";

                    if (!is_dir($folder)) {
                        mkdir($folder, 0755, true);
                    }

                    $nama_tanda_tangan =
                        bin2hex(random_bytes(16)) . "." . $ekstensi;

                    $tujuan = $folder . $nama_tanda_tangan;

                    if (!move_uploaded_file(
                        $_FILES["tanda_tangan"]["tmp_name"],
                        $tujuan
                    )) {
                        $pesan = "Gambar tanda tangan gagal disimpan.";
                    }
                }
            }
        }
    }

    if ($pesan == "") {
        $sql = $koneksi->prepare(
            "INSERT INTO notulen
            (user_id, seksi, judul, tanggal, waktu, tempat,
            pimpinan, peserta, agenda, pembahasan, keputusan,
            tindak_lanjut, notulis_nama, notulis_nip, tanda_tangan)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );

        if ($sql) {
            $sql->bind_param(
                "issssssssssssss",
                $user_id,
                $kasi,
                $judul,
                $tanggal,
                $waktu,
                $tempat,
                $pimpinan,
                $peserta,
                $agenda,
                $pembahasan,
                $keputusan,
                $tindak_lanjut,
                $notulis_nama,
                $notulis_nip,
                $nama_tanda_tangan
            );

            if ($sql->execute()) {
                $id_notulen = $koneksi->insert_id;

                $sql->close();
                $koneksi->close();

                header(
                    "Location: cetak-notulen.php?id=" . $id_notulen
                );
                exit;
            } else {
                $pesan = "Notulen gagal disimpan.";
            }

            $sql->close();
        } else {
            $pesan = "Query gagal: " . $koneksi->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Pengisian Notulen SIPENA</title>

    <link rel="stylesheet" href="../asset/css/style.css">

    <style>
        .form-notulen {
            max-width: 850px;
            margin: 30px auto;
            padding: 25px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 3px 12px #00000012;
        }

        .form-notulen h2 {
            color: #17365d;
        }

        .form-notulen label {
            display: block;
            margin-top: 15px;
            margin-bottom: 6px;
            font-weight: bold;
        }

        .form-notulen input,
        .form-notulen textarea {
            width: 100%;
            padding: 11px;
            border: 1px solid #ccc;
            border-radius: 6px;
            box-sizing: border-box;
            font: inherit;
        }

        .form-notulen textarea {
            min-height: 100px;
        }

        .form-notulen button {
            margin-top: 20px;
            padding: 12px 20px;
            background: #17365d;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        .pesan-error {
            color: #a00000;
            background: #ffe5e5;
            padding: 12px;
        }
    </style>
</head>

<body>

<div class="form-notulen">

    <h2>Pengisian Notulen Rapat</h2>
    <p>SIPENA Kecamatan Gantar</p>

    <?php if ($pesan != "") : ?>
        <p class="pesan-error">
            <?= htmlspecialchars($pesan) ?>
        </p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">

        <label>Judul Rapat</label>
        <input type="text" name="judul" required>

        <label>Hari / Tanggal</label>
        <input type="date" name="tanggal" required>

        <label>Waktu Rapat</label>
        <input type="text" name="waktu"
               placeholder="Contoh: 09.00 WIB - selesai" required>

        <label>Tempat Rapat</label>
        <input type="text" name="tempat"
               placeholder="Contoh: Ruang Rapat Kecamatan Gantar" required>

        <label>Pimpinan Rapat</label>
        <input type="text" name="pimpinan" required>

        <label>Peserta Rapat</label>
        <textarea name="peserta"
                  placeholder="Tuliskan peserta rapat" required></textarea>

        <label>Agenda Rapat</label>
        <textarea name="agenda" required></textarea>

        <label>Pembahasan Rapat</label>
        <textarea name="pembahasan" required></textarea>

        <label>Hasil Keputusan</label>
        <textarea name="keputusan" required></textarea>

        <label>Tindak Lanjut</label>
        <textarea name="tindak_lanjut"></textarea>

        <hr style="margin-top:25px;">

        <h3>Identitas Pembuat Notulen</h3>

        <label>Nama Notulis</label>
        <input type="text" name="notulis_nama" required>

        <label>NIP Notulis</label>
        <input type="text" name="notulis_nip" required>

        <label>Gambar Tanda Tangan</label>
        <input type="file" name="tanda_tangan"
               accept=".png,.jpg,.jpeg,image/png,image/jpeg">

        <small>
            Format PNG/JPG, maksimal 2 MB. Tanda tangan bersifat opsional.
        </small>

        <br>

        <button type="submit" name="simpan">
            Simpan dan Cetak Notulen
        </button>

    </form>
<br>
    <p>
        <a href="../dashboard.php" style="color: #17365d; font-weight: bold; text-decoration: none;">Kembali ke Dashboard</a>
    </p>

</div>

</body>
</html>