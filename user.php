<?php
// users.php
session_start();
require_once 'config/database.php';

// Proteksi: Hanya Admin yang bisa masuk
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: dashboard.php');
    exit;
}

$conn = getConnection();
$success = '';
$error = '';

// Logika Ganti Role
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_role') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Token tidak valid.';
    } else {
        $target_id = (int)$_POST['user_id'];
        $new_role = $_POST['role'];

        // Cegah admin mengubah role dirinya sendiri (opsional, agar tidak terkunci)
        if ($target_id === $_SESSION['user_id']) {
            $error = 'Anda tidak bisa mengubah role akun Anda sendiri.';
        } else {
            $stmt = $conn->prepare("UPDATE pengguna SET role = ? WHERE id = ?");
            $stmt->bind_param("si", $new_role, $target_id);
            if ($stmt->execute()) {
                $success = 'Role berhasil diperbarui.';
            }
            $stmt->close();
        }
    }
}

// Ambil semua pengguna
$result = $conn->query("SELECT id, nama_lengkap, email, role FROM pengguna ORDER BY nama_lengkap ASC");
$users = $result->fetch_all(MYSQLI_ASSOC);
$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manajemen Pengguna - PupukHub</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <nav class="bg-emerald-800 text-white p-4">
        <div class="container mx-auto flex justify-between">
            <h1 class="font-bold">PupukHub Admin</h1>
            <a href="dashboard.php" class="text-sm bg-emerald-700 px-3 py-1 rounded">Kembali ke Dashboard</a>
        </div>
    </nav>

    <main class="container mx-auto px-6 py-10">
        <h2 class="text-2xl font-bold mb-6">Manajemen Pengguna</h2>

        <?php if ($success): ?>
            <div class="bg-green-100 text-green-700 p-3 rounded mb-4"><?= $success ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4"><?= $error ?></div>
        <?php endif; ?>

        <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
            <table class="w-full text-left">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-6 py-4 text-sm font-bold">Nama</th>
                        <th class="px-6 py-4 text-sm font-bold">Email</th>
                        <th class="px-6 py-4 text-sm font-bold">Role Saat Ini</th>
                        <th class="px-6 py-4 text-sm font-bold">Aksi Ganti Role</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td class="px-6 py-4"><?= htmlspecialchars($user['nama_lengkap']) ?></td>
                        <td class="px-6 py-4"><?= htmlspecialchars($user['email']) ?></td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 rounded text-xs font-bold <?= $user['role'] === 'admin' ? 'bg-purple-100 text-purple-700' : 'bg-gray-100 text-gray-700' ?>">
                                <?= strtoupper($user['role']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <form method="POST" class="flex items-center gap-2">
                                <?= csrfField() ?>
                                <input type="hidden" name="action" value="change_role">
                                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                <select name="role" class="border rounded px-2 py-1 text-sm">
                                    <option value="pengguna" <?= $user['role'] === 'pengguna' ? 'selected' : '' ?>>Pengguna</option>
                                    <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                </select>
                                <button type="submit" class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700">Update</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>