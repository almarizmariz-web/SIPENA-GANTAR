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

$id = (int) ($_POST["id"] ?? 0);

if ($id < 1) {
    exit("ID absensi tidak valid.");
}

$sql = "DELETE FROM absensi WHERE id = ?";

$stmt = $koneksi->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: ../pages/absensi.php?status=dihapus");
    exit;
}

echo "Gagal menghapus data absensi.";
?>