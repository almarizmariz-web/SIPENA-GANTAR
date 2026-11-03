<?php
session_start();
require "../config/database.php";

// 1. Cek login
if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit;
}

// 2. Cek metode request
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: ../pages/dokumen.php");
    exit;
}

// 3. Ambil data input form
$judul = trim($_POST["judul_dokumen"] ?? "");
$kategori = $_POST["kategori"] ?? "";
$user_id = (int) $_SESSION["user_id"];

// Hanya mengizinkan kategori Foto dan Video
$kategoriDiizinkan = ["Foto", "Video"];

if ($judul == "" || !in_array($kategori, $kategoriDiizinkan)) {
    die("Judul atau kategori dokumen tidak valid. Hanya menerima Foto atau Video.");
}

// 4. Validasi upload berkas
if (!isset($_FILES["file_dokumen"]) || $_FILES["file_dokumen"]["error"] != UPLOAD_ERR_OK) {
    die("File gagal diunggah atau tidak ada file yang dipilih.");
}

$file = $_FILES["file_dokumen"];
$ukuran = $file["size"];
$tmp = $file["tmp_name"];

// Validasi ukuran file (Maksimal 5 MB)
if ($ukuran > 5 * 1024 * 1024) {
    die("Ukuran file terlalu besar. Maksimal adalah 5 MB.");
}

// 5. Cek Tipe MIME secara aman di server (Mencegah manipulasi ekstensi palsu)
$info = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($info, $tmp);
finfo_close($info);

// Daftar MIME Type Foto dan Video yang diperbolehkan
$jenisFile = [
    // FOTO
    "image/jpeg" => "jpg",
    "image/png"  => "png",
    "image/webp" => "webp",
    // VIDEO
    "video/mp4"        => "mp4",
    "video/x-matroska" => "mkv",
    "video/x-msvideo"  => "avi"
];

if (!isset($jenisFile[$mime])) {
    die("Format file ditolak! Skrip ini hanya menerima berkas Foto (JPG/PNG/WEBP) atau Video (MP4/MKV/AVI).");
}

$ekstensi = $jenisFile[$mime];

// 6. Validasi keamanan tambahan khusus gambar
if (strpos($mime, "image/") === 0) {
    if (getimagesize($tmp) === false) {
        die("File gambar rusak atau tidak valid.");
    }
}

// 7. Penentuan folder penyimpanan fisik berkas
$folder = "../uploads/dokumen/";
if (!is_dir($folder)) {
    mkdir($folder, 0755, true);
}

// 8. Membuat nama acak unik fisik berkas di server
$namaBaru = bin2hex(random_bytes(16)) . "." . $ekstensi;
$tujuan = $folder . $namaBaru;

// 9. Pindahkan berkas dari temporary ke folder server
if (!move_uploaded_file($tmp, $tujuan)) {
    die("File gagal disimpan ke server. Periksa hak akses folder uploads.");
}

// 10. Masukkan ke database ($namaBaru disimpan ke nama_file)
$sql = $koneksi->prepare(
    "INSERT INTO dokumen (user_id, nama_dokumen, jenis, nama_file) VALUES (?, ?, ?, ?)"
);

$sql->bind_param("isss", $user_id, $judul, $kategori, $namaBaru);

if ($sql->execute()) {
    header("Location: ../pages/dokumen.php?status=sukses");
    exit;
}

// Jika database gagal, hapus file fisik yang sempat tersimpan agar tidak menjadi sampah
if (file_exists($tujuan)) {
    unlink($tujuan);
}

die("Data dokumen gagal disimpan ke database. Error: " . $koneksi->error);
?>
