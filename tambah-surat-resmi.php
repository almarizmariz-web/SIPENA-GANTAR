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

if (!isset($_FILES["file_surat"]) ||
    $_FILES["file_surat"]["error"] !== UPLOAD_ERR_OK) {
    header("Location: ../pages/surat-resmi.php?status=gagal");
    exit;
}

$file = $_FILES["file_surat"];

if ($file["size"] > 5 * 1024 * 1024) {
    die("Ukuran file maksimal 5 MB.");
}

$nama_file = basename($file["name"]);
$tipe = mime_content_type($file["tmp_name"]);

// Pastikan file benar-benar PDF.
$handle = fopen($file["tmp_name"], "rb");
$header = fread($handle, 5);
fclose($handle);

if ($tipe !== "application/pdf" || $header !== "%PDF-") {
    die("File harus berupa PDF yang valid.");
}

// Buat nama file acak agar tidak bentrok.
$file_baru = bin2hex(random_bytes(16)) . ".pdf";

$folder = __DIR__ . "/../uploads/surat-resmi/";

if (!is_dir($folder)) {
    mkdir($folder, 0755, true);
}

$tujuan = $folder . $file_baru;

if (!move_uploaded_file($file["tmp_name"], $tujuan)) {
    die("File gagal disimpan.");
}

$user_id = $_SESSION["user_id"];

$stmt = mysqli_prepare($koneksi, "
    INSERT INTO surat_resmi (user_id, nama_file, file_surat)
    VALUES (?, ?, ?)
");

mysqli_stmt_bind_param($stmt, "iss", $user_id, $nama_file, $file_baru);

if (mysqli_stmt_execute($stmt)) {
    header("Location: ../pages/surat-resmi.php?status=berhasil");
    exit;
}

// Hapus file jika penyimpanan database gagal.
unlink($tujuan);

header("Location: ../pages/surat-resmi.php?status=gagal");
exit;
?>