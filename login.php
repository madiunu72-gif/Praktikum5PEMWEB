<?php
// auth/login.php
session_start();
require_once '../config/db.php';

if (isset($_SESSION['user_id'])) {
    header('Location: ../dashboard.php');
    exit;
}

$error = '';

// --- Rate Limiting sederhana: max 5 percobaan per 5 menit ---
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['login_time']     = time();
}

// Reset jika sudah lebih dari 5 menit
if (time() - $_SESSION['login_time'] > 300) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['login_time']     = time();
}

$blocked = $_SESSION['login_attempts'] >= 5;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($blocked) {
        $sisa = 300 - (time() - $_SESSION['login_time']);
        $error = "Terlalu banyak percobaan login. Coba lagi dalam {$sisa} detik.";
    } else {
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $error = 'Email dan password wajib diisi.';
        } else {
            $conn = getConnection();

            $stmt = $conn->prepare('SELECT id, nama_lengkap, password FROM pengguna WHERE email = ?');
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();

                if (password_verify($password, $user['password'])) {
                    // Berhasil — reset percobaan
                    $_SESSION['login_attempts'] = 0;
                    $_SESSION['user_id']        = $user['id'];
                    $_SESSION['user_nama']      = $user['nama_lengkap'];

                    $stmt->close();
                    $conn->close();

                    header('Location: ../dashboard.php');
                    exit;
                } else {
                    $_SESSION['login_attempts']++;
                    $sisa_coba = 5 - $_SESSION['login_attempts'];
                    $error = "Password salah. Sisa percobaan: {$sisa_coba}x.";
                }
            } else {
                $_SESSION['login_attempts']++;
                $error = 'Email tidak terdaftar. Silakan daftar terlebih dahulu.';
            }

            $stmt->close();
            $conn->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - PupukHub</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 flex items-center justify-center min-h-screen">

    <div class="bg-white p-8 rounded-xl shadow-lg w-full max-w-md border-t-4 border-emerald-600">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-extrabold text-emerald-700">PupukHub</h1>
            <p class="text-gray-500 mt-2 text-sm">Masuk menggunakan akun yang sudah terdaftar</p>
        </div>

        <?php if ($error): ?>
            <div class="mb-4 bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg text-sm">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-5">
            <div>
                <label class="block text-sm font-semibold text-gray-700">Email Address</label>
                <input type="email" name="email" required
                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                    placeholder="nama@gmail.com"
                    <?= $blocked ? 'disabled' : '' ?>
                    class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none transition">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700">Password</label>
                <div class="relative mt-1">
                    <input type="password" name="password" id="passwordInput" required
                        placeholder="Masukkan password kamu"
                        <?= $blocked ? 'disabled' : '' ?>
                        class="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none transition pr-10">
                    <button type="button" onclick="togglePassword()"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 text-xs font-semibold">
                        👁
                    </button>
                </div>
            </div>
            <button type="submit" <?= $blocked ? 'disabled' : '' ?>
                class="w-full bg-emerald-600 text-white py-3 rounded-lg font-bold hover:bg-emerald-700 transform hover:scale-[1.02] transition-all shadow-md disabled:opacity-50 disabled:cursor-not-allowed">
                Masuk Sekarang
            </button>
        </form>

        <div class="mt-8 pt-6 border-t border-gray-100 text-center">
            <p class="text-sm text-gray-600">
                Belum punya akun?
                <a href="register.php" class="text-emerald-600 font-bold hover:underline">Daftar di sini</a>
            </p>
        </div>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('passwordInput');
            input.type = input.type === 'password' ? 'text' : 'password';
        }
    </script>
</body>
</html>