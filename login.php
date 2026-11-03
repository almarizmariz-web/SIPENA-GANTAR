<?php 
session_start(); 
// Jika sudah login, langsung ke dashboard 
if (isset($_SESSION['user_id'])) { 
    header('Location: dashboard.php'); 
    exit; 
} 
$pesan = $_GET['pesan'] ?? ''; 
?> 
<!DOCTYPE html> 
<html lang="id"> 
<head> 
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Login SIPENA</title> 
    <link rel="stylesheet" href="asset/css/style.css">
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
<body class="halaman-login"> 
    <div class="login-box"> 
        <img src="img/download (1).jpg" class="login-logo-img" alt="Logo Kecamatan Gantar"> 
        <h2>SIPENA GANTAR</h2> 
        <p>Sistem Informasi Pengelolaan Notulen</p> 

        <?php if ($pesan != '') { ?> 
            <p class="pesan-login"> <?php echo htmlspecialchars($pesan); ?> </p> 
        <?php } ?> 

        <form action="proses/proses-login.php" method="POST"> 
            <label for="nip">NIP</label> 
            <input type="text" name="nip" id="nip" placeholder="Masukkan NIP" required> 

            <label for="password">Password</label> 
            <input type="password" name="password" id="password" placeholder="Masukkan password" required> 
            
            <!-- Pilihan Tampilkan Password -->
            <label class="show-password-container">
                <input type="checkbox" id="toggle-password"> Tampilkan Password
            </label>

            <button type="submit" name="login"> Masuk </button> 
        </form> 
    
    </div> 

    <!-- Script JavaScript untuk mengubah tipe input password -->
    <script>
        const passwordInput = document.getElementById('password');
        const toggleCheckbox = document.getElementById('toggle-password');

        toggleCheckbox.addEventListener('change', function() {
            if (this.checked) {
                passwordInput.type = 'text';
            } else {
                passwordInput.type = 'password';
            }
        });
    </script>
</body> 
</html>
