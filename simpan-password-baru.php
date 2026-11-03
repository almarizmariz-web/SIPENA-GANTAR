<?php

require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: ../login.php");
    exit;
}

$token = $_POST["token"] ?? "";
$password = $_POST["password"] ?? "";
$ulangiPassword = $_POST["ulangi_password"] ?? "";

if (strlen($token) != 64) {
    die("Tautan tidak valid.");
}

if (strlen($password) < 8) {
    die("Password minimal 8 karakter.");
}

if ($password != $ulangiPassword) {
    die("Password tidak sama.");
}

$tokenHash = hash("sha256", $token);

// Periksa token
$stmt = $koneksi->prepare(
    "SELECT id, user_id FROM password_resets
    WHERE token = ?
    AND kadaluarsa > NOW()
    AND digunakan = 0
    LIMIT 1"
);

$stmt->bind_param("s", $tokenHash);
$stmt->execute();

$hasil = $stmt->get_result();
$data = $hasil->fetch_assoc();

if (!$data) {
    die("Tautan sudah digunakan atau kedaluwarsa.");
}

// Buat hash password
$passwordHash = password_hash(
    $password,
    PASSWORD_DEFAULT
);

// Simpan password baru
$ubah = $koneksi->prepare(
    "UPDATE users SET password = ? WHERE id = ?"
);

$ubah->bind_param(
    "si",
    $passwordHash,
    $data["user_id"]
);

if ($ubah->execute()) {

    // Tandai token sudah digunakan
    $updateToken = $koneksi->prepare(
        "UPDATE password_resets
        SET digunakan = 1
        WHERE id = ? AND digunakan = 0"
    );

    $updateToken->bind_param("i", $data["id"]);
    $updateToken->execute();

    echo "<script>
        alert('Password berhasil diganti!');
        window.location='../login.php';
    </script>";

} else {

    echo "Gagal mengganti password.";

}

?>