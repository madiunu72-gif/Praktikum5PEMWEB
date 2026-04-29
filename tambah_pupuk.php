<?php 
session_start(); 
if($_SESSION['role'] != 'admin') header("location:../login.php"); 
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Stok - Admin PUPUKHUB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../asset/style.css">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f8f9fa; }
        .card-form { border: none; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .btn-save { background-color: #27ae60; color: white; font-weight: 600; border: none; }
        .btn-save:hover { background-color: #219150; color: white; }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card card-form p-4">
                    <div class="text-center mb-4">
                        <h2 class="fw-bold text-success">Tambah Stok Pupuk</h2>
                        <p class="text-muted">Masukkan detail informasi pupuk baru</p>
                    </div>
                    
                    <form action="../proses/proses_tambah.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nama Pupuk</label>
                            <input type="text" name="nama_pupuk" class="form-control" placeholder="Contoh: Urea, NPK" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Jenis Pupuk</label>
                            <input type="text" name="jenis_pupuk" class="form-control" placeholder="Contoh: Subsidi, Non-Subsidi" required>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Jumlah Stok (Unit)</label>
                                <input type="number" name="stok" class="form-control" placeholder="0" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Harga per Sak</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="harga" class="form-control" placeholder="0" required>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-save p-3">💾 Simpan Data Pupuk</button>
                            <a href="dashboard.php" class="btn btn-outline-secondary">Kembali ke Dashboard</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>