<?php
include '../config/koneksi.php';

$nama  = $_POST['nama_pupuk'];
$jenis = $_POST['jenis_pupuk'];
$stok  = $_POST['stok'];
$harga = $_POST['harga'];

$query = mysqli_query($conn, "INSERT INTO stok_pupuk (nama_pupuk, jenis_pupuk, stok, harga) VALUES ('$nama', '$jenis', '$stok', '$harga')");

header("location:../admin/dashboard.php");
?>