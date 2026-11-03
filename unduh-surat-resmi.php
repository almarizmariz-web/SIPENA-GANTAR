<?php
session_start();

require_once "../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit;
}

$user_id = (int) $_SESSION["user_id"];
$peran = $_SESSION["peran"] ?? "pegawai";
$id = (int) ($_GET["id"] ?? 0);

// Cari surat berdasarkan ID
if ($peran == "admin") {

    $stmt = $koneksi->prepare(
        "SELECT file_surat FROM surat_resmi WHERE id = ?"
    );

    $stmt->bind_param("i", $id);

} else {

    $stmt = $koneksi->prepare(
        "SELECT file_surat FROM surat_resmi
         WHERE id = ? AND user_id = ?"
    );

    $stmt->bind_param("ii", $id, $user_id);
}

$stmt->execute();

$hasil = $stmt->get_result();
$data = $hasil->fetch_assoc();

if (!$data) {
    die("Surat tidak ditemukan atau kamu tidak memiliki akses.");
}

// Ambil nama file yang aman
$namaFile = basename($data["file_surat"]);

$lokasi = "../uploads/surat-resmi/" . $namaFile;

if (!is_file($lokasi)) {
    die("File surat tidak ditemukan.");
}

// Tampilkan PDF di browser
header("Content-Type: application/pdf");
header("Content-Disposition: inline; filename=\"surat-rapat.pdf\"");
header("Content-Length: " . filesize($lokasi));
header("X-Content-Type-Options: nosniff");

readfile($lokasi);
exit;
?>