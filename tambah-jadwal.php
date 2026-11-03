
<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Jadwal Rapat</title>

    <link rel="stylesheet" href="../asset/css/jadwal.css">
</head>
<body>

    <div class="jadwal-header">
        <h1>Tambah Jadwal Rapat</h1>
        <p>Tambahkan kegiatan rapat Kecamatan Gantar</p>
    </div>

    <div class="konten-jadwal">

        <h2>Formulir Jadwal</h2>

        <form action="../proses/tambah-jadwal.php"
              method="POST"
              class="form-jadwal">

            <label>Kasi</label>
            <input type="text" name="seksi"
                   placeholder="Contoh: Pemerintahan" required>

            <label>Nama Kegiatan</label>
            <input type="text" name="kegiatan"
                   placeholder="Nama kegiatan rapat" required>

            <label>Tanggal dan Jam</label>
            <input type="datetime-local" name="tanggal" required>

            <label>Batas Pengumpulan Notulen</label>
            <input type="date" name="batas_notulen">

            <label>Keterangan</label>
            <textarea name="keterangan"
                      placeholder="Keterangan kegiatan rapat"></textarea>

            <button type="submit" class="tombol">
                Simpan Jadwal
            </button>

            <a href="jadwal.php" class="tombol-kembali">
                Kembali
            </a>

        </form>

    </div>

</body>
</html>
