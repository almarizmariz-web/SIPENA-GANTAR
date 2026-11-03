<?php
session_start();
require "../config/database.php";

// Periksa apakah pengguna sudah login.
if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit;
}

// Hanya menerima permintaan POST.
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../pages/daftar-notulen.php");
    exit;
}

// Ambil ID notulen.
$id = isset($_POST["id"]) ? (int) $_POST["id"] : 0;

if ($id < 1) {
    header("Location: ../pages/daftar-notulen.php?status=gagal");
    exit;
}

// Inisialisasi variabel statement
$stmt = null;

// Admin boleh menghapus semua notulen.
// Pegawai hanya boleh menghapus notulen miliknya.
if ($_SESSION["peran"] === "admin") {
    $sql = "DELETE FROM notulen WHERE id = ?";
    $stmt = $koneksi->prepare($sql);
    $stmt->bind_param("i", $id);
} else {
    $id_user = $_SESSION["user_id"];
    $sql = "DELETE FROM notulen WHERE id = ? AND user_id = ?";
    $stmt = $koneksi->prepare($sql);
    $stmt->bind_param("ii", $id, $id_user);
}

// Jalankan query dan cek efeknya
if ($stmt && $stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        $stmt->close();
        // Disarankan menutup koneksi utama jika tidak ada query lagi bawahnya
        // $koneksi->close(); 
        header("Location: ../pages/daftar-notulen.php?status=hapus");
        exit;
    }
}

// Jika gagal atau baris tidak ada yang terhapus (karena bukan miliknya)
if ($stmt) {
    $stmt->close();
}
header("Location: ../pages/daftar-notulen.php?status=gagal");
exit;
?>
