<?php
session_start();
include '../config/koneksi.php';

$username = $_POST['username'];
$password = $_POST['password'];

$query = mysqli_query($conn, "SELECT * FROM users WHERE username='$username' AND password='$password'");
$data = mysqli_fetch_assoc($query);

if (mysqli_num_rows($query) > 0) {
    // SINKRONISASI SESSION
    $_SESSION['id']       = $data['id'];
    $_SESSION['username'] = $data['username']; // Tambahkan ini agar tidak "mental"
    $_SESSION['nama']     = $data['nama_lengkap'];
    $_SESSION['role']     = $data['role'];

    if ($data['role'] == 'admin') {
        header("location:../admin/dashboard.php");
    } else {
        header("location:../user/dashboard.php");
    }
} else {
    echo "<script>alert('Login Gagal! Username atau Password salah.'); window.location='../login.php';</script>";
}
?>