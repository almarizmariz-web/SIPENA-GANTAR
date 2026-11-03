
<?php
require "config/database.php";

$nip = "ADMIN001";
$nama = "Administrator SIPENA";
$password = "GantiPasswordKuat123!";
$kasi = "Administrator";
$peran = "admin";
$status = "aktif";

// Cek apakah NIP sudah ada
$sql = $koneksi->prepare(
    "SELECT id FROM users WHERE nip = ?"
);

$sql->bind_param("s", $nip);
$sql->execute();

$hasil = $sql->get_result();

if ($hasil->num_rows > 0) {
    die("Akun admin sudah ada.");
}

$sql->close();

// Mengamankan password
$hash = password_hash($password, PASSWORD_DEFAULT);

// Menyimpan akun admin
$sql = $koneksi->prepare(
    "INSERT INTO users
    (nip, nama, password, peran, kasi, status)
    VALUES (?, ?, ?, ?, ?, ?)"
);

if (!$sql) {
    die("Query gagal: " . $koneksi->error);
}

$sql->bind_param(
    "ssssss",
    $nip,
    $nama,
    $hash,
    $peran,
    $kasi,
    $status
);

if ($sql->execute()) {
    echo "Akun admin berhasil dibuat!";
} else {
    echo "Gagal membuat akun: " . $sql->error;
}

$sql->close();
$koneksi->close();
?>
