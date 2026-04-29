<?php
session_start();
include '../config/koneksi.php';

// Proteksi halaman: Pastikan hanya admin yang bisa masuk
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("location:../login.php");
    exit();
}

// Ambil nama admin untuk sapaan
$nama_admin = isset($_SESSION['nama']) ? $_SESSION['nama'] : $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - PUPUKHUB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f8f9fa; }
        .navbar-custom { background-color: #1b5e20; padding: 15px 0; }
        .navbar-brand { color: white !important; font-weight: 700; letter-spacing: 1px; }
        .card-table { border: none; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); }
        .table thead { background-color: #f1f8e9; }
        .btn-tambah { background-color: #2ecc71; color: white; font-weight: 600; border-radius: 8px; }
        .btn-tambah:hover { background-color: #27ae60; color: white; }
        .price-text { color: #2e7d32; font-weight: 600; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-custom shadow-sm mb-5">
        <div class="container">
            <a class="navbar-brand" href="#">PUPUKHUB ADMIN</a>
            <div class="ms-auto">
                <a href="../proses/logout.php" class="btn btn-outline-light btn-sm px-4" onclick="return confirm('Yakin ingin keluar?')">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="row align-items-center mb-4">
            <div class="col-md-6">
                <h2 class="fw-bold m-0">Manajemen Stok & Harga</h2>
                <p class="text-muted">Selamat bekerja, <span class="text-dark fw-bold"><?php echo $nama_admin; ?></span></p>
            </div>
            <div class="col-md-6 text-md-end">
                <a href="tambah_pupuk.php" class="btn btn-tambah px-4 py-2 shadow-sm">+ Tambah Pupuk Baru</a>
            </div>
        </div>

        <div class="card card-table overflow-hidden">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4 py-3">Nama Pupuk</th>
                                <th>Jenis</th>
                                <th>Harga per Karung</th>
                                <th>Stok Tersedia</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $query = mysqli_query($conn, "SELECT * FROM stok_pupuk ORDER BY id DESC");
                            if(mysqli_num_rows($query) > 0) {
                                while($row = mysqli_fetch_assoc($query)) {
                                    // Format Rupiah
                                    $harga_rp = "Rp " . number_format($row['harga'], 0, ',', '.');
                            ?>
                            <tr>
                                <td class="ps-4 fw-bold text-capitalize"><?php echo $row['nama_pupuk']; ?></td>
                                <td class="text-capitalize"><?php echo $row['jenis_pupuk']; ?></td>
                                <td class="price-text"><?php echo $harga_rp; ?></td>
                                <td>
                                    <span class="badge bg-light text-dark border px-3 py-2">
                                        <?php echo $row['stok']; ?> Unit
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <a href="edit_pupuk.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning px-3">Edit</a>
                                        <a href="../proses/proses_hapus.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger px-3" onclick="return confirm('Hapus data ini?')">Hapus</a>
                                    </div>
                                </td>
                            </tr>
                            <?php 
                                } 
                            } else {
                                echo "<tr><td colspan='5' class='text-center py-5 text-muted'>Belum ada data pupuk.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>