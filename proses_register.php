<?php
include '../config/koneksi.php';

$nama = $_POST['nama_lengkap'];
$user = $_POST['username'];
$pass = $_POST['password']; // Disarankan menggunakan password_hash untuk keamanan produksi

$query = mysqli_query($conn, "INSERT INTO users (nama_lengkap, username, password, role) VALUES ('$nama', '$user', '$pass', 'user')");

if($query) {
    echo "<script>alert('Registrasi Berhasil! Silakan Login.'); window.location='../login.php';</script>";
} else {
    echo "<script>alert('Registrasi Gagal!'); window.location='../register.php';</script>";
}
?>