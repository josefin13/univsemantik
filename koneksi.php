<?php
// Konfigurasi Database InfinityFree
$host     = 'sql206.infinityfree.com';
$username = 'if0_42996096';
$password = 'LQs8wCpx8y7U';
$dbname   = 'if0_42996096_universitassemantik';


// Membuat koneksi ke server MySQL
$koneksi = mysqli_connect($host, $username, $password, $dbname);

// Memeriksa status koneksi
if (!$koneksi) {
    die("Gagal terhubung ke database MySQL: " . mysqli_connect_error());
}

// Mengatur charset ke utf8mb4 agar mendukung seluruh karakter
mysqli_set_charset($koneksi, "utf8mb4");
?>