```php
<?php
session_start();
include "../config/database.php";

if (!isset($_SESSION["user_id"])) {
    http_response_code(403);
    exit;
}

$id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;
$user_id = $_SESSION["user_id"];
$peran = $_SESSION["peran"];

if ($peran == "admin") {
    $sql = "SELECT * FROM dokumen WHERE id = ? AND jenis = 'Foto'";
} else {
    $sql = "SELECT * FROM dokumen
            WHERE id = ? AND user_id = ? AND jenis = 'Foto'";
}

$stmt = mysqli_prepare($koneksi, $sql);

if ($peran == "admin") {
    mysqli_stmt_bind_param($stmt, "i", $id);
} else {
    mysqli_stmt_bind_param($stmt, "ii", $id, $user_id);
}

mysqli_stmt_execute($stmt);
$hasil = mysqli_stmt_get_result($stmt);
$foto = mysqli_fetch_assoc($hasil);

if (!$foto) {
    http_response_code(404);
    exit;
}

$lokasi = __DIR__ . "/../uploads/dokumen/" . basename($foto["nama_file"]);

if (!is_file($lokasi)) {
    die("File foto tidak ditemukan: " . $lokasi);
}

$ekstensi = strtolower(pathinfo($lokasi, PATHINFO_EXTENSION));

if ($ekstensi == "jpg" || $ekstensi == "jpeg") {
    $tipe = "image/jpeg";
} elseif ($ekstensi == "png") {
    $tipe = "image/png";
} else {
    http_response_code(403);
    exit;
}

header("Content-Type: " . $tipe);
header("X-Content-Type-Options: nosniff");

readfile($lokasi);
exit;
?>
```