<?php
session_start();
include "../config/database.php";

// 1. Cek login
if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit;
}

// 2. Pastikan permintaan berasal dari form POST tombol hapus
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: ../pages/dokumen.php");
    exit;
}

$id = (int) ($_POST["id"] ?? 0);
$user_id = $_SESSION["user_id"];
$peran = $_SESSION["peran"];

// 3. Ambil data dokumen dari database untuk mengetahui nama file fisiknya
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

// Periksa apakah data ada di database
if (!$dokumen) {
    die("Dokumen tidak ditemukan atau kamu tidak memiliki akses untuk menghapusnya.");
}

// 4. Kunci lokasi folder dan gabungkan dengan nama file acak dari database
$folder_uploads = realpath(__DIR__ . "/../uploads/dokumen/");
$lokasi_file = $folder_uploads . '/' . basename($dokumen["nama_file"]);

// 5. Eksekusi penghapusan data di database terlebih dahulu
$sql_hapus = "DELETE FROM dokumen WHERE id = ?";
$stmt_hapus = mysqli_prepare($koneksi, $sql_hapus);
mysqli_stmt_bind_param($stmt_hapus, "i", $id);

if (mysqli_stmt_execute($stmt_hapus)) {
    // 6. Jika data di DB berhasil terhapus, hapus file fisik acak di server agar tidak jadi sampah
    if (!empty($dokumen["nama_file"]) && is_file($lokasi_file)) {
        unlink($lokasi_file);
    }
    
    // Kembali ke halaman utama dengan status sukses
    header("Location: ../pages/dokumen.php?status=hapus_sukses");
    exit;
} else {
    die("Gagal menghapus data dokumen dari database: " . mysqli_error($koneksi));
}
?>
