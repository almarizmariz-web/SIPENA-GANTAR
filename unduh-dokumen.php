<?php
session_start();
include "../config/database.php";

// Cek login
if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit;
}

$id = (int) ($_GET["id"] ?? 0);
$user_id = $_SESSION["user_id"];
$peran = $_SESSION["peran"];

// 1. Kueri disesuaikan untuk mengambil semua kategori dokumen
if ($peran == "admin") {
    $sql = "SELECT * FROM dokumen WHERE id = ?";
    $stmt = mysqli_prepare($koneksi, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
} else {
    $sql = "SELECT * FROM dokumen WHERE id = ? AND user_id = ?";
    $stmt = mysqli_prepare($koneksi, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $id, $user_id);
}

mysqli_stmt_execute($stmt);
$hasil = mysqli_stmt_get_result($stmt);
$dokumen = mysqli_fetch_assoc($hasil);

// Periksa data dokumen di database
if (!$dokumen) {
    die("Dokumen tidak ditemukan atau kamu tidak memiliki akses.");
}

// 2. Kunci lokasi folder dengan realpath agar presisi
$folder_uploads = realpath(__DIR__ . "/../uploads/dokumen/");
if (!$folder_uploads) {
    die("Folder penyimpanan tidak ditemukan di server.");
}

$lokasi = $folder_uploads . '/' . basename($dokumen["nama_file"]);

// Pastikan file fisik benar-benar ada di folder
if (!is_file($lokasi)) {
    die("File fisik tidak ditemukan di folder penyimpanan.");
}

// Ambil ekstensi asli dari file yang tersimpan
$ekstensi = strtolower(pathinfo($dokumen["nama_file"], PATHINFO_EXTENSION));

// 3. Mapping Content-Type yang sesuai dengan aplikasi SIPENA Anda
$content_types = [
    'pdf'  => 'application/pdf',
    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png'  => 'image/png'
];
$mime_type = $content_types[$ekstensi] ?? 'application/octet-stream';

// 4. Buat nama file unduhan bersih berdasarkan judul dokumen yang diinput user
$nama_unduhan = preg_replace('/[^a-zA-Z0-9_-]/', '_', $dokumen["nama_dokumen"]);
if (empty($nama_unduhan)) {
    $nama_unduhan = "dokumen_sipena";
}

// Bersihkan output buffer agar file tidak corrupt / rusak
while (ob_get_level() > 0) {
    ob_end_clean();
}

// 5. Kirim file ke browser untuk langsung diunduh dengan nama asli dokumennya
header("Content-Type: " . $mime_type);
header("Content-Disposition: attachment; filename=\"" . $nama_unduhan . "." . $ekstensi . "\"");
header("Content-Length: " . filesize($lokasi));
header("Cache-Control: private, max-age=0, must-revalidate");
header("Pragma: public");

readfile($lokasi);
exit;
?>
