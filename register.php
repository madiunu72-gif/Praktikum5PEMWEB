<?php
// auth/register.php
session_start();
require_once '../config/db.php';

if (isset($_SESSION['user_id'])) {
    header('Location: ../dashboard.php');
    exit;
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama     = trim($_POST['nama_lengkap'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($nama) || empty($email) || empty($password)) {
        $error = 'Semua field wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || !str_ends_with($email, '@gmail.com')) {
        $error = 'Email harus menggunakan format @gmail.com.';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter.';
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $error = 'Password harus mengandung minimal 1 huruf besar.';
    } elseif (!preg_match('/[0-9]/', $password)) {
        $error = 'Password harus mengandung minimal 1 angka.';
    } else {
        $conn = getConnection();

        $stmt = $conn->prepare('SELECT id FROM pengguna WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = 'Email sudah terdaftar. Silakan gunakan email lain.';
            $stmt->close();
        } else {
            $stmt->close();
            $hashed = password_hash($password, PASSWORD_BCRYPT);

            $stmt = $conn->prepare('INSERT INTO pengguna (nama_lengkap, email, password) VALUES (?, ?, ?)');
            $stmt->bind_param('sss', $nama, $email, $hashed);

            if ($stmt->execute()) {
                $success = 'Registrasi berhasil! Silakan login.';
                $_POST   = [];
            } else {
                $error = 'Terjadi kesalahan. Coba lagi.';
            }
            $stmt->close();
        }

        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi - PupukHub</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 flex items-center justify-center min-h-screen">

    <div class="bg-white p-8 rounded-xl shadow-lg w-full max-w-md border-t-4 border-blue-600">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-gray-800">Daftar Akun</h1>
            <p class="text-gray-500 text-sm mt-1">Data tersimpan ke database MySQL</p>
        </div>

        <?php if ($error): ?>
            <div class="mb-4 bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg text-sm">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">
                <?= htmlspecialchars($success) ?>
                <a href="login.php" class="font-bold underline ml-1">Login sekarang</a>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Lengkap</label>
                <input type="text" name="nama_lengkap" required
                    value="<?= htmlspecialchars($_POST['nama_lengkap'] ?? '') ?>"
                    placeholder="Nama lengkap kamu"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none transition">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Email</label>
                <input type="email" name="email" required
                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                    placeholder="nama@gmail.com"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none transition">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Password</label>
                <div class="relative">
                    <input type="password" name="password" id="passwordInput" required
                        placeholder="Min. 6 karakter, 1 huruf besar, 1 angka"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none transition pr-10">
                    <button type="button" onclick="togglePassword()"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 text-xs font-semibold">
                        👁
                    </button>
                </div>
                <ul class="mt-2 text-xs space-y-1 list-disc list-inside">
                    <li id="req-length" class="text-gray-400">Minimal 6 karakter</li>
                    <li id="req-upper"  class="text-gray-400">Minimal 1 huruf besar</li>
                    <li id="req-number" class="text-gray-400">Minimal 1 angka</li>
                </ul>
            </div>
            <button type="submit"
                class="w-full bg-blue-600 text-white py-2 rounded-lg font-bold hover:bg-blue-700 transition shadow-md">
                Daftar Sekarang
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-gray-600">
            Sudah punya akun?
            <a href="login.php" class="text-blue-600 font-bold hover:underline">Login di sini</a>
        </p>
    </div>

    <script>
        const input     = document.getElementById('passwordInput');
        const reqLength = document.getElementById('req-length');
        const reqUpper  = document.getElementById('req-upper');
        const reqNumber = document.getElementById('req-number');

        function check(el, condition) {
            el.classList.toggle('text-green-500', condition);
            el.classList.toggle('text-gray-400', !condition);
        }

        input.addEventListener('input', () => {
            const v = input.value;
            check(reqLength, v.length >= 6);
            check(reqUpper,  /[A-Z]/.test(v));
            check(reqNumber, /[0-9]/.test(v));
        });

        function togglePassword() {
            input.type = input.type === 'password' ? 'text' : 'password';
        }
    </script>
</body>
</html>