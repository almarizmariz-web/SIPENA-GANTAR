<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit;
}

include "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: ../pages/absensi.php");
    exit;
}

$notulen_id = (int) ($_POST["notulen_id"] ?? 0);
$nama_pegawai = trim($_POST["nama_pegawai"] ?? "");
$nip = trim($_POST["nip"] ?? "");
$keterangan = $_POST["keterangan"] ?? "";

if (
    $notulen_id < 1 ||
    $nama_pegawai == "" ||
    strlen($nama_pegawai) > 100 ||
    strlen($nip) > 30
) {
    exit("Data absensi tidak valid.");
}

if (
    $keterangan != "Hadir" &&
    $keterangan != "Izin" &&
    $keterangan != "Tidak Hadir"
) {
    exit("Status kehadiran tidak valid.");
}

// Pastikan notulen benar-benar ada
$cek = $koneksi->prepare(
    "SELECT id FROM notulen WHERE id = ?"
);

$cek->bind_param("i", $notulen_id);
$cek->execute();

if ($cek->get_result()->num_rows == 0) {
    exit("Notulen tidak ditemukan.");
}

// Simpan absensi
$sql = "INSERT INTO absensi
        (notulen_id, nama_pegawai, nip, keterangan)
        VALUES (?, ?, NULLIF(?, ''), ?)";

$stmt = $koneksi->prepare($sql);

$stmt->bind_param(
    "isss",
    $notulen_id,
    $nama_pegawai,
    $nip,
    $keterangan
);

if ($stmt->execute()) {
    header("Location: ../pages/absensi.php?status=berhasil");
    exit;
}

echo "Gagal menyimpan data absensi.";
?>