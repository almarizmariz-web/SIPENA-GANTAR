```php
<?php
session_start();

require_once "../config/database.php";

// Pastikan tombol login ditekan
if (isset($_POST["login"])) {

    $nip = trim($_POST["nip"]);
    $password = $_POST["password"];

    // Cari akun berdasarkan NIP
    $sql = $koneksi->prepare(
        "SELECT * FROM users
         WHERE nip = ? AND status = 'aktif'
         LIMIT 1"
    );

    $sql->bind_param("s", $nip);
    $sql->execute();

    $hasil = $sql->get_result();
    $user = $hasil->fetch_assoc();

    // Periksa password
    if ($user && password_verify($password, $user["password"])) {

        // Periksa jenis akun
        if (
            $user["peran"] != "admin" &&
            $user["peran"] != "pegawai"
        ) {
            header("Location: ../login.php?pesan=Peran akun tidak dikenali");
            exit;
        }

        // Buat sesi login baru
        session_regenerate_id(true);

        $_SESSION["user_id"] = $user["id"];
        $_SESSION["nip"] = $user["nip"];
        $_SESSION["nama"] = $user["nama"];
        $_SESSION["peran"] = $user["peran"];
        $_SESSION["kasi"] = $user["kasi"];

        // Admin dan pegawai masuk ke dashboard
        header("Location: ../dashboard.php");
        exit;

    } else {

        header("Location: ../login.php?pesan=NIP atau password salah, atau akun belum aktif");
        exit;
    }

    $sql->close();

} else {

    header("Location: ../login.php");
    exit;
}
?>
