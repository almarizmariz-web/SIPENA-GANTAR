<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit;
}

include "../config/database.php";

// Mencari kolom judul notulen
$kolom_judul = "";

$hasil_kolom = $koneksi->query("SHOW COLUMNS FROM notulen");

if ($hasil_kolom) {
    $daftar_kolom = [];

    while ($kolom = $hasil_kolom->fetch_assoc()) {
        $daftar_kolom[] = $kolom["Field"];
    }

    $pilihan_kolom = [
        "judul",
        "judul_notulen",
        "judul_rapat",
        "nama_rapat",
        "perihal",
        "kegiatan",
        "topik"
    ];

    foreach ($pilihan_kolom as $pilihan) {
        if (in_array($pilihan, $daftar_kolom)) {
            $kolom_judul = $pilihan;
            break;
        }
    }
}

// Mengambil daftar notulen
$daftar_notulen = $koneksi->query(
    "SELECT * FROM notulen ORDER BY id DESC"
);

// Mengambil data yang akan diedit
$data_edit = null;

if (isset($_GET["edit"])) {
    $id_edit = (int) $_GET["edit"];

    $stmt = $koneksi->prepare(
        "SELECT * FROM absensi WHERE id = ?"
    );

    $stmt->bind_param("i", $id_edit);
    $stmt->execute();

    $hasil_edit = $stmt->get_result();
    $data_edit = $hasil_edit->fetch_assoc();

    $stmt->close();
}

// Menghitung ringkasan absensi
$jumlah_hadir = 0;
$jumlah_izin = 0;
$jumlah_tidak_hadir = 0;

$hasil_ringkasan = $koneksi->query(
    "SELECT keterangan, COUNT(*) AS jumlah
     FROM absensi
     GROUP BY keterangan"
);

if ($hasil_ringkasan) {
    while ($baris = $hasil_ringkasan->fetch_assoc()) {
        if ($baris["keterangan"] == "Hadir") {
            $jumlah_hadir = $baris["jumlah"];
        } elseif ($baris["keterangan"] == "Izin") {
            $jumlah_izin = $baris["jumlah"];
        } elseif ($baris["keterangan"] == "Tidak Hadir") {
            $jumlah_tidak_hadir = $baris["jumlah"];
        }
    }
}

// Mengambil semua data absensi
$daftar_absensi = $koneksi->query(
    "SELECT * FROM absensi ORDER BY id DESC"
);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Absensi Pegawai - SIPENA</title>

    <link rel="stylesheet" href="../asset/css/absensi.css">
</head>

<body>

<div class="konten-absensi">

    <div class="absensi-header">
        <h1>Absensi Pegawai</h1>

        <p>
            Kelola data kehadiran pegawai berdasarkan notulen rapat.
        </p>
    </div>

    <!-- Ringkasan absensi -->
    <div class="ringkasan-absensi">

        <div class="kartu-absensi">
            <h3>Hadir</h3>
            <p><?php echo $jumlah_hadir; ?></p>
        </div>

        <div class="kartu-absensi">
            <h3>Izin</h3>
            <p><?php echo $jumlah_izin; ?></p>
        </div>

        <div class="kartu-absensi">
            <h3>Tidak Hadir</h3>
            <p><?php echo $jumlah_tidak_hadir; ?></p>
        </div>

    </div>

    <!-- Pesan berhasil -->
    <?php if (isset($_GET["status"])) { ?>

        <p class="pesan-absensi">
            <?php
            if ($_GET["status"] == "berhasil") {
                echo "Data absensi berhasil ditambahkan.";
            } elseif ($_GET["status"] == "diubah") {
                echo "Data absensi berhasil diubah.";
            } elseif ($_GET["status"] == "dihapus") {
                echo "Data absensi berhasil dihapus.";
            }
            ?>
        </p>

    <?php } ?>

    <!-- Form absensi -->
    <div class="form-absensi">

        <h2>
            <?php
            echo $data_edit
                ? "Ubah Data Absensi"
                : "Tambah Data Absensi";
            ?>
        </h2>

        <form
            action="<?php
            echo $data_edit
                ? "../proses/edit-absensi.php"
                : "../proses/tambah-absensi.php";
            ?>"
            method="POST"
        >

            <?php if ($data_edit) { ?>
                <input
                    type="hidden"
                    name="id"
                    value="<?php echo $data_edit["id"]; ?>"
                >
            <?php } ?>

            <label for="notulen_id">Pilih Notulen</label>

            <select name="notulen_id" id="notulen_id" required>

                <option value="">-- Pilih Judul Notulen --</option>

                <?php if ($daftar_notulen) { ?>

                    <?php while ($notulen = $daftar_notulen->fetch_assoc()) { ?>

                        <?php
                        $judul = "";

                        if ($kolom_judul != "") {
                            $judul = $notulen[$kolom_judul] ?? "";
                        }

                        if ($judul == "") {
                            $judul = "Notulen #" . $notulen["id"];
                        }

                        $terpilih = "";

                        if (
                            $data_edit &&
                            $data_edit["notulen_id"] == $notulen["id"]
                        ) {
                            $terpilih = "selected";
                        }
                        ?>

                        <option
                            value="<?php echo $notulen["id"]; ?>"
                            <?php echo $terpilih; ?>
                        >
                            <?php echo htmlspecialchars($judul); ?>
                        </option>

                    <?php } ?>

                <?php } ?>

            </select>

            <?php if ($kolom_judul == "") { ?>
                <small>
                    Nama kolom judul belum ditemukan.
                    Periksa struktur tabel notulen agar judul rapat
                    dapat ditampilkan.
                </small>
            <?php } ?>

            <label for="nama_pegawai">Nama Pegawai</label>

            <input
                type="text"
                name="nama_pegawai"
                id="nama_pegawai"
                placeholder="Masukkan nama pegawai"
                value="<?php
                echo htmlspecialchars(
                    $data_edit["nama_pegawai"] ?? ""
                );
                ?>"
                required
            >

            <label for="nip">NIP</label>

            <input
                type="text"
                name="nip"
                id="nip"
                placeholder="Masukkan NIP pegawai"
                value="<?php
                echo htmlspecialchars($data_edit["nip"] ?? "");
                ?>"
            >

            <label for="keterangan">Keterangan Kehadiran</label>

            <select name="keterangan" id="keterangan" required>

                <option value="">-- Pilih Kehadiran --</option>

                <option
                    value="Hadir"
                    <?php
                    echo (
                        isset($data_edit["keterangan"]) &&
                        $data_edit["keterangan"] == "Hadir"
                    ) ? "selected" : "";
                    ?>
                >
                    Hadir
                </option>

                <option
                    value="Izin"
                    <?php
                    echo (
                        isset($data_edit["keterangan"]) &&
                        $data_edit["keterangan"] == "Izin"
                    ) ? "selected" : "";
                    ?>
                >
                    Izin
                </option>

                <option
                    value="Tidak Hadir"
                    <?php
                    echo (
                        isset($data_edit["keterangan"]) &&
                        $data_edit["keterangan"] == "Tidak Hadir"
                    ) ? "selected" : "";
                    ?>
                >
                    Tidak Hadir
                </option>

            </select>

            <button type="submit" class="tombol">
                <?php
                echo $data_edit
                    ? "Simpan Perubahan"
                    : "Tambah Absensi";
                ?>
            </button>

            <?php if ($data_edit) { ?>
                <a href="absensi.php" class="tombol-kembali">
                    Batal
                </a>
            <?php } ?>

        </form>

    </div>

    <!-- Tabel data absensi -->
    <div class="tabel-bungkus">

        <h2>Daftar Absensi Pegawai</h2>

        <table>

            <thead>
                <tr>
                    <th>No.</th>
                    <th>Notulen</th>
                    <th>Nama Pegawai</th>
                    <th>NIP</th>
                    <th>Kehadiran</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>

                <?php if ($daftar_absensi && $daftar_absensi->num_rows > 0) { ?>

                    <?php $nomor = 1; ?>

                    <?php while ($absensi = $daftar_absensi->fetch_assoc()) { ?>

                        <?php
                        $judul_tabel = "Notulen #" . $absensi["notulen_id"];

                        $id_notulen = (int) $absensi["notulen_id"];

                        if ($kolom_judul != "") {
                            $stmt_judul = $koneksi->prepare(
                                "SELECT `$kolom_judul`
                                 FROM notulen
                                 WHERE id = ?"
                            );

                            $stmt_judul->bind_param("i", $id_notulen);
                            $stmt_judul->execute();

                            $hasil_judul = $stmt_judul->get_result();
                            $data_judul = $hasil_judul->fetch_assoc();

                            if (
                                $data_judul &&
                                !empty($data_judul[$kolom_judul])
                            ) {
                                $judul_tabel = $data_judul[$kolom_judul];
                            }

                            $stmt_judul->close();
                        }
                        ?>

                        <tr>

                            <td><?php echo $nomor++; ?></td>

                            <td>
                                <?php echo htmlspecialchars($judul_tabel); ?>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $absensi["nama_pegawai"]
                                );
                                ?>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $absensi["nip"] ?? "-"
                                );
                                ?>
                            </td>

                            <td>
                                <span class="status">
                                    <?php
                                    echo htmlspecialchars(
                                        $absensi["keterangan"]
                                    );
                                    ?>
                                </span>
                            </td>

                            <td class="aksi">

                                <a
                                    href="absensi.php?edit=<?php
                                    echo $absensi["id"];
                                    ?>"
                                    class="tombol-edit"
                                >
                                    Edit
                                </a>

                                <form
                                    action="../proses/hapus-absensi.php"
                                    method="POST"
                                    onsubmit="return confirm(
                                        'Yakin ingin menghapus absensi ini?'
                                    );"
                                >

                                    <input
                                        type="hidden"
                                        name="id"
                                        value="<?php echo $absensi["id"]; ?>"
                                    >

                                    <button
                                        type="submit"
                                        class="tombol-hapus"
                                    >
                                        Hapus
                                    </button>

                                </form>

                            </td>

                        </tr>

                    <?php } ?>

                <?php } else { ?>

                    <tr>
                        <td colspan="6" class="data-kosong">
                            Belum ada data absensi.
                        </td>
                    </tr>

                <?php } ?>

            </tbody>

        </table>

    </div>

    <br>

    <a href="../dashboard.php" class="tombol-kembali">
        Kembali ke Dashboard
    </a>

</div>

</body>
</html>