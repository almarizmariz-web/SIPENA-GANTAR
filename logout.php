<?php

session_start();

// Hapus semua data login
$_SESSION = array();

// Hapus sesi
session_destroy();

// Kembali ke halaman login
header("Location: login.php");
exit;

?>