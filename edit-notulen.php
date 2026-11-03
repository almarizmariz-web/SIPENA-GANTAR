
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

    $sql = "SELECT * FROM notulen
            WHERE id = ? AND user_id = ?";
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

$pesan = "";

if (isset($_POST["simpan"])) {
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

    if (
        $judul == "" || $tanggal == "" ||
        $pimpinan == "" || $agenda == "" ||
        $pembahasan == "" || $keputusan == ""
    ) {
        $pesan = "Mohon isi semua kolom wajib.";
    } else {
        $sql = "UPDATE notulen SET
                judul = ?,
                tanggal = ?,
                waktu = ?,
                tempat = ?,
                pimpinan = ?,
                peserta = ?,
                agenda = ?,
                pembahasan = ?,
                keputusan = ?,
                tindak_lanjut = ?,
                notulis_nama = ?,
                notulis_nip = ?
                WHERE id = ?";

        if ($_SESSION["peran"] != "admin") {
            $sql .= " AND user_id = ?";
        }

        $stmt = $koneksi->prepare($sql);

        if ($_SESSION["peran"] == "admin") {
            $stmt->bind_param(
                "ssssssssssssi",
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
                $id
            );
        } else {
            $stmt->bind_param(
                "ssssssssssssii",
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
                $id,
                $id_user
            );
        }

        if ($stmt->execute()) {
            header("Location: detail-notulen.php?id=" . $id);
            exit;
        } else {
            $pesan = "Notulen gagal diperbarui.";
        }
    }

    // Tampilkan kembali data yang baru diketik jika validasi gagal.
    if ($pesan != "") {
        $data["judul"] = $judul;
        $data["tanggal"] = $tanggal;
        $data["waktu"] = $waktu;
        $data["tempat"] = $tempat;
        $data["pimpinan"] = $pimpinan;
        $data["peserta"] = $peserta;
        $data["agenda"] = $agenda;
        $data["pembahasan"] = $pembahasan;
        $data["keputusan"] = $keputusan;
        $data["tindak_lanjut"] = $tindak_lanjut;
        $data["notulis_nama"] = $notulis_nama;
        $data["notulis_nip"] = $notulis_nip;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Notulen SIPENA</title>

    <style>
        body {
            margin: 0;
            padding: 25px;
            background: #f1f5f9;
            font-family: Arial, sans-serif;
            color: #243247;
        }

        .container {
            max-width: 850px;
            margin: auto;
            padding: 25px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px #00000010;
        }

        h2 {
            color: #163b68;
        }

        label {
            display: block;
            margin-top: 16px;
            margin-bottom: 6px;
            font-weight: bold;
        }

        input, textarea {
            width: 100%;
            box-sizing: border-box;
            padding: 11px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font: inherit;
        }

        textarea {
            min-height: 100px;
            resize: vertical;
        }

        .pesan {
            padding: 12px;
            background: #fff1f0;
            color: #a12622;
            border-radius: 6px;
        }

        .tombol {
            display: inline-block;
            padding: 11px 16px;
            margin-top: 20px;
            border: none;
            border-radius: 6px;
            color: white;
            text-decoration: none;
            cursor: pointer;
            font-size: 14px;
        }

        .simpan {
            background: #176b45;
        }

        .kembali {
            background: #526477;
        }
    </style>
</head>

<body>
<div class="container">

    <h2>Edit Notulen Rapat</h2>
    <p>Perbaiki data rapat, lalu tekan Simpan Perubahan.</p>

    <?php if ($pesan != "") { ?>
        <p class="pesan"><?= aman($pesan) ?></p>
    <?php } ?>

    <form method="POST">

        <label>Judul Rapat *</label>
        <input type="text" name="judul"
               value="<?= aman($data["judul"]) ?>" required>

        <label>Tanggal Rapat *</label>
        <input type="date" name="tanggal"
               value="<?= aman($data["tanggal"]) ?>" required>

        <label>Waktu Rapat</label>
        <input type="text" name="waktu"
               value="<?= aman($data["waktu"]) ?>"
               placeholder="Contoh: 09.00 WIB sampai selesai">

        <label>Tempat Rapat</label>
        <input type="text" name="tempat"
               value="<?= aman($data["tempat"]) ?>"
               placeholder="Contoh: Aula Kecamatan Gantar">

        <label>Pimpinan Rapat *</label>
        <input type="text" name="pimpinan"
               value="<?= aman($data["pimpinan"]) ?>" required>

        <label>Peserta Rapat</label>
        <textarea name="peserta"><?= aman($data["peserta"]) ?></textarea>

        <label>Agenda Rapat *</label>
        <textarea name="agenda" required><?= aman($data["agenda"]) ?></textarea>

        <label>Pembahasan *</label>
        <textarea name="pembahasan" required><?= aman($data["pembahasan"]) ?></textarea>

        <label>Hasil Keputusan *</label>
        <textarea name="keputusan" required><?= aman($data["keputusan"]) ?></textarea>

        <label>Tindak Lanjut</label>
        <textarea name="tindak_lanjut"><?= aman($data["tindak_lanjut"]) ?></textarea>

        <label>Nama Notulis</label>
        <input type="text" name="notulis_nama"
               value="<?= aman($data["notulis_nama"]) ?>">

        <label>NIP Notulis</label>
        <input type="text" name="notulis_nip"
               value="<?= aman($data["notulis_nip"]) ?>">

        <br>

        <button type="submit" name="simpan" class="tombol simpan">
            Simpan Perubahan
        </button>

        <a href="detail-notulen.php?id=<?= (int) $data["id"] ?>"
           class="tombol kembali">
            Batal
        </a>

    </form>

</div>
</body>
</html>
