<?php
include '../config/koneksi.php';
$id = $_GET['id'];
mysqli_query($conn, "DELETE FROM stok_pupuk WHERE id='$id'");
header("location:../admin/dashboard.php");
?>