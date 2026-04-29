<?php
session_start();
include '../config/koneksi.php';

/** @var mysqli $conn */ // 

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("location:../login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("location:dashboard.php");
    exit();
}

$id = mysqli_real_escape_string($conn, $_GET['id']);
$query = mysqli_query($conn, "SELECT * FROM stok_pupuk WHERE id='$id'");

if (!$query) {
    die("Error Database: " . mysqli_error($conn));
}

$data = mysqli_fetch_array($query);

if (!$data) {
    echo "<script>alert('Data tidak ditemukan!'); window.location='dashboard.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Data Pupuk - PUPUKHUB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f4f7f6; }
        .edit-container { max-width: 500px; margin: 50px auto; }
        .card-edit { border: none; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .btn-update { background-color: #f39c12; color: white; border: none; border-radius: 10px; font-weight: 600; padding: 12px; transition: 0.3s; }
        .btn-update:hover { background-color: #e67e22; color: white; }
        .btn-batal { border-radius: 10px; padding: 10px; border: 1px solid #ced4da; color: #6c757d; text-decoration: none; display: block; text-align: center; margin-top: 10px; transition: 0.3s; }
        .btn-batal:hover { background-color: #f8f9fa; color: #333; }
        label { font-weight: 600; color: #333; margin-bottom: 5px; }
        .form-control { border-radius: 10px; padding: 12px; border: 1px solid #dee2e6; margin-bottom: 20px; }
        .form-control:focus { border-color: #f39c12; box-shadow: 0 0 0 0.25rem rgba(243, 156, 18, 0.25); }
    </style>
</head>
<body>

<div class="edit-container px-3">
    <div class="card card-edit">
        <div class="card-body p-4 p-md-5">
            <h2 class="text-center fw-bold mb-2" style="color: #f1c40f;">Edit Data Pupuk</h2>
            <p class="text-center text-muted mb-5">Perbarui informasi stok dan harga pupuk di bawah ini</p>

            <form action="../proses/proses_edit.php" method="POST">
                <input type="hidden" name="id" value="<?php echo $data['id']; ?>">

                <div class="form-group">
                    <label>Nama Pupuk</label>
                    <input type="text" name="nama_pupuk" class="form-control" value="<?php echo $data['nama_pupuk']; ?>" required>
                </div>

                <div class="form-group">
                    <label>Jenis Pupuk</label>
                    <select name="jenis_pupuk" class="form-control" required>
                        <option value="subsidi" <?php echo ($data['jenis_pupuk'] == 'subsidi') ? 'selected' : ''; ?>>Subsidi</option>
                        <option value="non subsidi" <?php echo ($data['jenis_pupuk'] == 'non subsidi') ? 'selected' : ''; ?>>Non Subsidi</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Jumlah Stok (Unit)</label>
                    <input type="number" name="stok" class="form-control" value="<?php echo $data['stok']; ?>" required>
                </div>

                <div class="form-group">
                    <label>Harga per Sak (Rp)</label>
                    <input type="number" name="harga" class="form-control" value="<?php echo $data['harga']; ?>" placeholder="Contoh: 150000" required>
                </div>

                <button type="submit" class="btn btn-update w-100 mt-2">🔄 Perbarui Data</button>
                <a href="dashboard.php" class="btn-batal">Batal & Kembali</a>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>