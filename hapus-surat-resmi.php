<?php
session_start();

require_once "../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../pages/surat-resmi.php");
    exit;
}

if (
    !isset($_POST["csrf_surat"]) ||
    !hash_equals($_SESSION["csrf_surat"] ?? "", $_POST["csrf_surat"])
) {
    die("Permintaan tidak valid.");
}

$id = isset($_POST["id"]) ? (int) $_POST["id"] : 0;

$stmt = mysqli_prepare($koneksi, "
    SELECT * FROM surat_resmi WHERE id = ?
");

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$hasil = mysqli_stmt_get_result($stmt);
$surat = mysqli_fetch_assoc($hasil);

if (!$surat) {
    header("Location: ../pages/surat-resmi.php?status=gagal");
    exit;
}

// Admin boleh menghapus semua surat.
// Pegawai hanya boleh menghapus surat miliknya.
if (
    ($_SESSION["peran"] ?? "") !== "admin" &&
    $surat["user_id"] != $_SESSION["user_id"]
) {
    die("Kamu tidak memiliki izin menghapus surat ini.");
}

$lokasi = __DIR__ . "/../uploads/surat-resmi/" .
          basename($surat["file_surat"]);

// Hapus data dari database.
$hapus = mysqli_prepare($koneksi, "
    DELETE FROM surat_resmi WHERE id = ?
");

mysqli_stmt_bind_param($hapus, "i", $id);

if (mysqli_stmt_execute($hapus)) {

    // Hapus file PDF dari folder.
    if (is_file($lokasi)) {
        unlink($lokasi);
    }

    header("Location: ../pages/surat-resmi.php?status=dihapus");
    exit;
}

header("Location: ../pages/surat-resmi.php?status=gagal");
exit;
?>