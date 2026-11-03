<?php

require_once "../config/database.php";
require_once "../config/email.php";

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: ../lupa-password.php");
    exit;
}

$email = trim($_POST["email"]);

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("Format Gmail tidak valid.");
}

// Cari email di database
$stmt = $koneksi->prepare(
    "SELECT id, email FROM users WHERE email = ? LIMIT 1"
);

$stmt->bind_param("s", $email);
$stmt->execute();

$hasil = $stmt->get_result();
$user = $hasil->fetch_assoc();

$pesan = "Jika Gmail terdaftar, tautan reset akan dikirim. Silakan periksa kotak masuk.";

// Jika Gmail ditemukan
if ($user) {

    // Buat token
    $token = bin2hex(random_bytes(32));

    // Hash token sebelum disimpan
    $tokenHash = hash("sha256", $token);

    // Token berlaku 15 menit
    $kadaluarsa = date("Y-m-d H:i:s", time() + 900);

    // Hapus token lama milik akun ini
    $hapusLama = $koneksi->prepare(
        "DELETE FROM password_resets WHERE user_id = ?"
    );

    $hapusLama->bind_param("i", $user["id"]);
    $hapusLama->execute();

    // Simpan token baru
    $simpan = $koneksi->prepare(
        "INSERT INTO password_resets
        (user_id, token, kadaluarsa)
        VALUES (?, ?, ?)"
    );

    $simpan->bind_param(
        "iss",
        $user["id"],
        $tokenHash,
        $kadaluarsa
    );

    if ($simpan->execute()) {

        // Link reset password
        $link = "http://localhost:8080/SIPENA/reset-password.php?token=" . $token;

        try {

            $mail->addAddress($user["email"]);

            $mail->Subject = "Reset Password SIPENA";

            $mail->Body = "
                <h2>Reset Password SIPENA</h2>

                <p>Kami menerima permintaan untuk mengganti password akun SIPENA.</p>

                <p>Klik tombol berikut untuk membuat password baru:</p>

                <a href='$link'
                style='background:#145da0;
                color:white;
                padding:12px 20px;
                text-decoration:none;
                border-radius:5px;'>
                    Reset Password
                </a>

                <p>Tautan berlaku selama 15 menit.</p>

                <p>Jika kamu tidak meminta perubahan password,
                abaikan email ini.</p>
            ";

            $mail->send();

        } catch (Exception $e) {

            // Hapus token jika email gagal dikirim
            $hapus = $koneksi->prepare(
                "DELETE FROM password_resets WHERE token = ?"
            );

            $hapus->bind_param("s", $tokenHash);
            $hapus->execute();
        }
    }
}

echo "<script>
alert('$pesan');
window.location='../login.php';
</script>";

?>