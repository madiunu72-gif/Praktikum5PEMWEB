<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login - PUPUKHUB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f0f2f5; height: 100vh; display: flex; align-items: center; font-family: 'Poppins', sans-serif; }
        .card-login { border: none; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        .btn-login { background-color: #27ae60; color: white; border-radius: 8px; padding: 10px; font-weight: 600; }
        .btn-login:hover { background-color: #219150; color: white; }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="card card-login p-4">
                    <div class="text-center mb-4">
                        <h3 class="fw-bold text-success">PUPUKHUB</h3>
                        <p class="text-muted">Silakan masuk ke akun Anda</p>
                    </div>
                    <form action="proses/proses_login.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" class="form-control" placeholder="Masukkan username" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
                        </div>
                        <button type="submit" class="btn btn-login w-100 mb-3">Masuk</button>
                        <p class="text-center small">Belum punya akun? <a href="register.php" class="text-success text-decoration-none fw-bold">Daftar di sini</a></p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>