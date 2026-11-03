<?php
session_start();
require '../config/database.php';

// Cek login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$peran = $_SESSION['peran'];

$hasil = null;

// Ambil dokumen sesuai hak akses
if ($peran == 'admin') {
    $sql = $koneksi->prepare("SELECT dokumen.*, users.nama FROM dokumen JOIN users ON dokumen.user_id = users.id ORDER BY dokumen.dibuat DESC");
} else {
    $sql = $koneksi->prepare("SELECT dokumen.*, users.nama FROM dokumen JOIN users ON dokumen.user_id = users.id WHERE dokumen.user_id = ? ORDER BY dokumen.dibuat DESC");
    if ($sql) {
        $sql->bind_param("i", $user_id);
    }
}

if (!$sql) {
    die("Gagal mempersiapkan query database: " . $koneksi->error);
}

if ($sql->execute()) {
    $hasil = $sql->get_result();
} else {
    die("Gagal mengeksekusi query: " . $sql->error);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dokumen SIPENA Kecamatan Gantar</title>
    <link rel="stylesheet" href="../asset/css/style.css">
    <style>
        .halaman-dokumen { max-width: 1200px; margin: 25px auto; padding: 20px; box-sizing: border-box; }
        .kotak-dokumen { background: white; padding: 20px; margin-bottom: 20px; border-radius: 10px; box-shadow: 0 3px 10px #00000010; }
        .kotak-dokumen h2 { color: #17365d; }
        .form-dokumen label { display: block; margin-top: 15px; margin-bottom: 6px; font-weight: bold; }
        .form-dokumen input, .form-dokumen select { width: 100%; padding: 11px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 6px; }
        .btn-dokumen { display: inline-block; margin-top: 15px; padding: 10px 15px; background: #17365d; color: white; border: none; border-radius: 5px; text-decoration: none; cursor: pointer; text-align: center; }
        .tabel-dokumen { width: 100%; overflow-x: auto; }
        .tabel-dokumen table { width: 100%; min-width: 850px; border-collapse: collapse; }
        .tabel-dokumen th, .tabel-dokumen td { padding: 12px 10px; border: 1px solid #ddd; text-align: left; vertical-align: middle; }
        .tabel-dokumen th { background: #17365d; color: white; }
        .tombol-hapus { background: #b42318; margin-top: 0; }
        .aksi-kolom { display: flex; gap: 8px; align-items: center; }
        .form-inline { display: inline; margin: 0; }
        
        /* Gaya Khusus untuk Pratinjau Media */
        .preview-media { max-width: 180px; max-height: 120px; border-radius: 6px; border: 1px solid #ddd; display: block; background: #f9f9f9; }
        
        @media (max-width: 480px) {
            .halaman-dokumen { padding: 12px; margin: 10px auto; }
            .kotak-dokumen { padding: 15px; }
        }
    </style>
</head>
<body>
    <div class="halaman-dokumen">
        <h1>Pengelolaan Dokumen Rapat</h1>
        <p>SIPENA Kecamatan Gantar</p>
        
        <!-- PEMBERITAHUAN STATUS -->
        <?php if (isset($_GET['status']) && $_GET['status'] == 'sukses'): ?>
            <div style="padding: 12px; background: #d4edda; color: #155724; border-radius: 6px; margin-bottom: 15px; font-weight: bold;">
                ✓ Berkas baru berhasil diunggah dan disimpan!
            </div>
        <?php endif; ?>

        <div class="kotak-dokumen">
            <h2>Upload Dokumen (Khusus Foto / Video)</h2>
            <form class="form-dokumen" action="../proses/upload-dokumen.php" method="POST" enctype="multipart/form-data">
                <label>Judul Dokumen</label>
                <input type="text" name="judul_dokumen" maxlength="150" required placeholder="Contoh: Rapat Koordinasi PKH Subisdi">
                
                <label>Kategori Dokumen</label>
                <select name="kategori" required>
                    <option value="">-- Pilih Kategori --</option>
                    <option value="Foto">Foto Dokumentasi</option>
                    <option value="Video">Video Rapat</option>
                </select>
                
                <label>Pilih Berkas</label>
                <input type="file" name="file_dokumen" accept="image/*,video/*" required>
                <small style="display: block; margin-top: 5px; color: #666;">
                    Format yang diperbolehkan: JPG, JPEG, PNG, WEBP, MP4, MKV, AVI. Maksimal 5 MB.
                </small>
                
                <button type="submit" name="upload" class="btn-dokumen">Upload Dokumen</button>
            </form>
        </div>

        <div class="kotak-dokumen">
            <h2>Daftar Dokumen</h2>
            <div class="tabel-dokumen">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 5px;">No.</th>
                            <th>Judul & Informasi</th>
                            <th>Kategori</th>
                            <th style="width: 200px;">Pratinjau Media (Live Preview)</th>
                            <?php if ($peran == 'admin') : ?>
                                <th>Pengunggah</th>
                            <?php endif; ?>
                            <th>Tanggal Upload</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($hasil && $hasil->num_rows > 0) : ?>
                            <?php $no = 1; ?>
                            <?php while ($dokumen = $hasil->fetch_assoc()) : ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($dokumen['nama_dokumen']) ?></strong>
                                        <br>
                                        <small style="color: #666; font-family: monospace;"><?= htmlspecialchars($dokumen['nama_file']) ?></small>
                                    </td>
                                    <td>
                                        <span style="padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; background: <?= ($dokumen['jenis'] == 'Video') ? '#e3f2fd; color: #0d47a1;' : '#e8f5e9; color: #1b5e20;' ?>;">
                                            <?= htmlspecialchars($dokumen['jenis']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <!-- LOGIKA LIVE PREVIEW MEDIA -->
                                        <?php 
                                        $path_file = "../uploads/dokumen/" . $dokumen['nama_file'];
                                        if ($dokumen['jenis'] == 'Foto'): 
                                        ?>
                                            <img src="<?= $path_file ?>" class="preview-media" alt="Foto Dokumentasi">
                                        <?php elseif ($dokumen['jenis'] == 'Video'): ?>
                                            <video class="preview-media" controls preload="metadata">
                                                <source src="<?= $path_file ?>" type="video/mp4">
                                                Browser Anda tidak mendukung pemutar video html5.
                                            </video>
                                        <?php else: ?>
                                            <span style="color: #999; font-style: italic;">Tidak ada pratinjau</span>
                                        <?php endif; ?>
                                    </td>
                                    <?php if ($peran == 'admin') : ?>
                                        <td><?= htmlspecialchars($dokumen['nama']) ?></td>
                                    <?php endif; ?>
                                    <td><?= date('d M Y H:i', strtotime($dokumen['dibuat'])) ?> WIB</td>
                                    <td>
                                        <div class="aksi-kolom">
                                            <a class="btn-dokumen" style="margin-top:0; background: #2e7d32;" href="../proses/unduh-dokumen.php?id=<?= (int) $dokumen['id'] ?>">Unduh</a>
                                            <form action="../proses/hapus-dokumen.php" method="POST" class="form-inline" onsubmit="return confirm('Yakin ingin menghapus dokumen ini?')">
                                                <input type="hidden" name="id" value="<?= (int) $dokumen['id'] ?>">
                                                <button type="submit" class="btn-dokumen tombol-hapus">Hapus</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="<?= $peran == 'admin' ? 7 : 6 ?>" style="text-align: center; color: #999; padding: 30px 10px;">Belum ada berkas foto atau video yang diunggah.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <p><a href="../dashboard.php" style="color: #17365d; font-weight: bold; text-decoration: none;">← Kembali ke Dashboard</a></p>
    </div>
</body>
</html>
