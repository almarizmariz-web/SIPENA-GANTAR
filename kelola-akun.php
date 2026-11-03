
<?php
session_start();
require "../config/database.php";

// Hanya admin yang boleh membuka halaman ini
if (
    !isset($_SESSION["user_id"]) ||
    !isset($_SESSION["peran"]) ||
    $_SESSION["peran"] != "admin"
) {
    header("Location: ../login.php");
    exit;
}

// Membuat token keamanan
if (!isset($_SESSION["token"])) {
    $_SESSION["token"] = bin2hex(random_bytes(32));
}

$pesan = "";

// PROSES TAMBAH AKUN
if (isset($_POST["tambah"])) {

    // Memeriksa token
    if (
        !isset($_POST["token"]) ||
        !is_string($_POST["token"]) ||
        !hash_equals($_SESSION["token"], $_POST["token"])
    ) {
        die("Permintaan tidak valid!");
    }

    $nip = trim($_POST["nip"] ?? "");
    $nama = trim($_POST["nama"] ?? "");
    $password = $_POST["password"] ?? "";
    $email = trim($_POST["email"] ?? "");
    $kasi = trim($_POST["kasi"] ?? "");
    $status = $_POST["status"] ?? "";

    // Daftar Kasi yang diperbolehkan
  $daftar_kasi = [
    "pelayanan masyarakat",
    "tata pemerintahan",
    "kesejahteraan sosial",
    "keamanan dan ketertiban",
    "umum dan kepegawaian",
    "pemberdayaan masyarakat dan desa",
    "keuangan",
    "sekretaris camat",
    "camat"
];
    // Memeriksa data
    if ($nip == "" || $nama == "" || $password == "" || $kasi == "") {

        $pesan = "Semua kolom wajib harus diisi!";

    } elseif (strlen($nip) > 30 || strlen($nama) > 100) {

        $pesan = "NIP atau nama terlalu panjang!";

    } elseif (strlen($password) < 8) {

        $pesan = "Password minimal 8 karakter!";

    } elseif ($email != "" && !filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $pesan = "Format email tidak valid!";

    } elseif (!in_array($kasi, $daftar_kasi, true)) {

        $pesan = "Pilihan Kasi tidak valid!";

    } elseif ($status != "aktif" && $status != "menunggu") {

        $pesan = "Status akun tidak valid!";

    } else {

        // Memeriksa NIP
        $cek = $koneksi->prepare("
            SELECT id FROM users
            WHERE nip = ?
        ");

        if (!$cek) {
            die("Terjadi kesalahan pada database.");
        }

        $cek->bind_param("s", $nip);
        $cek->execute();

        $hasil_cek = $cek->get_result();

        if ($hasil_cek->num_rows > 0) {

            $pesan = "NIP sudah terdaftar!";

        } else {

            // Mengamankan password
            $password_hash = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            // Menambahkan akun dengan peran pegawai
            $tambah = $koneksi->prepare("
                INSERT INTO users
                (nip, nama, password, peran, email, kasi, status)
                VALUES (?, ?, ?, 'pegawai', ?, ?, ?)
            ");

            if (!$tambah) {
                die("Terjadi kesalahan pada database.");
            }

            $tambah->bind_param(
                "ssssss",
                $nip,
                $nama,
                $password_hash,
                $email,
                $kasi,
                $status
            );

            if ($tambah->execute()) {

                $tambah->close();
                $cek->close();

                header("Location: kelola-akun.php?pesan=tambah");
                exit;

            } else {

                $pesan = "Akun gagal ditambahkan!";
            }

            $tambah->close();
        }

        $cek->close();
    }
}


// PROSES HAPUS AKUN
if (isset($_POST["hapus"])) {

    // Memeriksa token
    if (
        !isset($_POST["token"]) ||
        !is_string($_POST["token"]) ||
        !hash_equals($_SESSION["token"], $_POST["token"])
    ) {
        die("Permintaan tidak valid!");
    }

    $id = (int) ($_POST["id"] ?? 0);

    // Mencegah menghapus akun sendiri
    if ($id <= 0 || $id == $_SESSION["user_id"]) {

        $pesan = "Akun sendiri atau ID tidak valid!";

    } else {

        // Memastikan akun yang akan dihapus adalah pegawai
        $cek = $koneksi->prepare("
            SELECT id FROM users
            WHERE id = ? AND peran = 'pegawai'
        ");

        $cek->bind_param("i", $id);
        $cek->execute();

        $hasil = $cek->get_result();

        if ($hasil->num_rows > 0) {

            $hapus = $koneksi->prepare("
                DELETE FROM users
                WHERE id = ? AND peran = 'pegawai'
            ");

            $hapus->bind_param("i", $id);

            if ($hapus->execute()) {

                $hapus->close();
                $cek->close();

                header("Location: kelola-akun.php?pesan=hapus");
                exit;

            } else {

                $pesan = "Akun gagal dihapus!";
            }

            $hapus->close();

        } else {

            $pesan = "Akun tidak ditemukan atau bukan akun pegawai!";
        }

        $cek->close();
    }
}


// PESAN SETELAH TAMBAH ATAU HAPUS
if (isset($_GET["pesan"])) {

    if ($_GET["pesan"] == "tambah") {
        $pesan = "Akun pegawai berhasil ditambahkan!";
    } elseif ($_GET["pesan"] == "hapus") {
        $pesan = "Akun pegawai berhasil dihapus!";
    }
}


// MENGAMBIL DAFTAR AKUN
$sql = $koneksi->query("
    SELECT id, nip, nama, email, kasi, peran, status
    FROM users
    ORDER BY id DESC
");

if (!$sql) {
    die("Data akun gagal ditampilkan.");
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Kelola Akun - SIPENA</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f2f5fa;
            margin: 0;
            padding: 25px;
        }

        .container {
            max-width: 1100px;
            margin: auto;
        }

        .kotak {
            background: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 25px;
            box-shadow: 0 2px 8px #ddd;
        }

        h2 {
            color: #173d70;
        }

        label {
            display: block;
            margin-top: 10px;
        }

        input, select {
            width: 100%;
            padding: 11px;
            margin: 7px 0 15px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button, .kembali {
            background: #17477e;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }

        .hapus {
            background: #c0392b;
        }

        .pesan {
            background: #e7f4ff;
            padding: 12px;
            margin-bottom: 15px;
            border-radius: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 850px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }

        th {
            background: #17477e;
            color: white;
        }

        .tabel {
            overflow-x: auto;
        }

        .form-hapus {
            margin: 0;
        }
            <style>
        /* Gaya tambahan untuk merapikan posisi checkbox */
        .show-password-container {
            display: flex;
            align-items: center;
            margin-top: -5px;
            margin-bottom: 15px;
            cursor: pointer;
            font-size: 14px;
        }
        .show-password-container input {
            margin: 0 8px 0 0;
            width: auto; /* Memastikan ukuran checkbox tidak penuh */
            cursor: pointer;
        }
    
    </style>

</head>

<body>

<div class="container">

    <a href="../dashboard.php" class="kembali">
        Kembali ke Dashboard
    </a>

    <!-- FORM TAMBAH AKUN -->
    <div class="kotak">

        <h2>Kelola Akun Pegawai</h2>

        <p>Tambah akun pegawai Kecamatan Gantar.</p>

        <?php if ($pesan != "") { ?>

            <div class="pesan">
                <?php echo htmlspecialchars($pesan); ?>
            </div>

        <?php } ?>

        <form method="POST">

            <input
                type="hidden"
                name="token"
                value="<?php echo htmlspecialchars($_SESSION["token"]); ?>"
            >

            <label>NIP Pegawai</label>

            <input
                type="text"
                name="nip"
                maxlength="30"
                placeholder="Masukkan NIP"
                required
            >

            <label>Nama Lengkap</label>

            <input
                type="text"
                name="nama"
                maxlength="100"
                placeholder="Masukkan nama pegawai"
                required
            >

         <label for="password-pegawai">Password</label>

<input
    type="password"
    name="password"
    id="password-pegawai"
    minlength="8"
    placeholder="Minimal 8 karakter"
    required
>

<label class="show-password-container">
    <input
        type="checkbox"
        id="toggle-password-pegawai"
    >
    Tampilkan Password
</label>

            <label>Kasi / Bagian</label>

            <select name="kasi" required>

                <option value="">-- Pilih Bagian --</option>

                <option value="pelayanan masyarakat">
                    Pelayanan Masyarakat
                </option>

                <option value="tata pemerintahan">
                    Tata Pemerintahan
                </option>

                <option value="kesejahteraan sosial">
                    Kesejahteraan Sosial
                </option>

                <option value="keamanan dan ketertiban">
                    Keamanan dan Ketertiban
                </option>

                <option value="umum dan kepegawaian">
                    Umum dan Kepegawaian
                </option>

                <option value="pemberdayaan masyarakat dan desa">
                    Pemberdayaan Masyarakat dan Desa
                </option>
<option value="keuangan">
    Keuangan / Bendahara
</option>
<option value="sekretaris camat">
    Sekretaris Camat
</option>

<option value="camat">
    Camat
</option>
            </select>

            <label>Status Akun</label>

            <select name="status" required>

                <option value="menunggu">
                    Menunggu Persetujuan
                </option>

                <option value="aktif">
                    Aktif
                </option>

            </select>

            <button type="submit" name="tambah">
                Tambah Akun
            </button>

        </form>

    </div>


    <!-- DAFTAR AKUN -->
    <div class="kotak">

        <h2>Daftar Akun Pegawai</h2>

        <div class="tabel">

            <table>

                <tr>
                    <th>No</th>
                    <th>NIP</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Kasi / Bagian</th>
                    <th>Peran</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>

                <?php
                $no = 1;

                while ($akun = $sql->fetch_assoc()) {
                ?>

                <tr>

                    <td><?php echo $no++; ?></td>

                    <td>
                        <?php echo htmlspecialchars($akun["nip"] ?? "-"); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($akun["nama"] ?? "-"); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($akun["email"] ?? "-"); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($akun["kasi"] ?? "-"); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($akun["peran"] ?? "-"); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($akun["status"] ?? "-"); ?>
                    </td>

                    <td>

                        <?php if ($akun["peran"] == "pegawai") { ?>

                            <form
                                method="POST"
                                class="form-hapus"
                                onsubmit="return confirm('Yakin ingin menghapus akun pegawai ini?');"
                            >

                                <input
                                    type="hidden"
                                    name="token"
                                    value="<?php echo htmlspecialchars($_SESSION["token"]); ?>"
                                >

                                <input
                                    type="hidden"
                                    name="id"
                                    value="<?php echo (int) $akun["id"]; ?>"
                                >

                                <button
                                    type="submit"
                                    name="hapus"
                                    class="hapus"
                                >
                                    Hapus
                                </button>

                            </form>

                        <?php } else { ?>

                            Akun Admin

                        <?php } ?>

                    </td>

                </tr>

                <?php } ?>

            </table>

        </div>

    </div>

</div>
<script>
    const passwordInput = document.getElementById("password-pegawai");
    const toggleCheckbox = document.getElementById("toggle-password-pegawai");

    toggleCheckbox.addEventListener("change", function() {

        if (this.checked) {
            passwordInput.type = "text";
        } else {
            passwordInput.type = "password";
        }

    });
</script>
</body>
</html>
