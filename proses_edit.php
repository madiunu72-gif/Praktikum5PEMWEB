<?php
include '../config/koneksi.php';

// Menangkap data yang dikirim dari form edit_pupuk.php
$id    = $_POST['id'];
$nama  = $_POST['nama_pupuk'];
$jenis = $_POST['jenis_pupuk'];
$stok  = $_POST['stok'];
$harga = $_POST['harga']; // VARIABEL BARU UNTUK HARGA

// Update data ke database (Pastikan kolom 'harga' ada di tabel stok_pupuk)
$query = mysqli_query($conn, "UPDATE stok_pupuk SET 
    nama_pupuk  = '$nama', 
    jenis_pupuk = '$jenis', 
    stok        = '$stok',
    harga       = '$harga' 
    WHERE id    = '$id'");

if($query) {
    // Jika berhasil, alihkan ke dashboard admin
    header("location:../admin/dashboard.php");
} else {
    // Jika gagal, tampilkan pesan error
    echo "Gagal mengupdate data: " . mysqli_error($conn);
}
?>