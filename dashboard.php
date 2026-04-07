<logout class="php"></logout><?php
// dashboard.php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit;
}

$conn    = getConnection();
$error   = '';
$success = '';

// =====================
// HAPUS DATA (POST + CSRF)
// =====================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'hapus') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Token tidak valid. Ulangi aksi.';
    } else {
        $id_hapus = (int) ($_POST['id'] ?? 0);
        $stmt = $conn->prepare('DELETE FROM distribusi WHERE id = ?');
        $stmt->bind_param('i', $id_hapus);
        $stmt->execute();
        $stmt->close();
        $success = 'Data berhasil dihapus.';
    }
}

// =====================
// TAMBAH DATA (POST + CSRF)
// =====================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'tambah') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Token tidak valid. Ulangi aksi.';
    } else {
        $petani  = trim($_POST['petani'] ?? '');
        $alamat  = trim($_POST['alamat'] ?? '');
        $jenis   = $_POST['jenis'] ?? '';
        $jumlah  = (int) ($_POST['jumlah'] ?? 0);
        $jenis_valid = ['Urea', 'NPK', 'Organik'];

        if (empty($petani) || empty($alamat) || !in_array($jenis, $jenis_valid) || $jumlah <= 0 || $jumlah > 99999) {
            $error = 'Semua field wajib diisi dengan benar. Jumlah maksimal 99.999 kg.';
        } else {
            $stmt = $conn->prepare('INSERT INTO distribusi (petani, alamat, jenis_pupuk, jumlah) VALUES (?, ?, ?, ?)');
            $stmt->bind_param('sssi', $petani, $alamat, $jenis, $jumlah);
            if ($stmt->execute()) {
                $success = 'Data distribusi berhasil disimpan.';
                $_POST   = [];
            } else {
                $error = 'Gagal menyimpan data. Coba lagi.';
            }
            $stmt->close();
        }
    }
}

// =====================
// EDIT DATA (POST + CSRF)
// =====================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Token tidak valid. Ulangi aksi.';
    } else {
        $id      = (int) ($_POST['id'] ?? 0);
        $petani  = trim($_POST['petani'] ?? '');
        $alamat  = trim($_POST['alamat'] ?? '');
        $jenis   = $_POST['jenis'] ?? '';
        $jumlah  = (int) ($_POST['jumlah'] ?? 0);
        $jenis_valid = ['Urea', 'NPK', 'Organik'];

        if (empty($petani) || empty($alamat) || !in_array($jenis, $jenis_valid) || $jumlah <= 0 || $jumlah > 99999) {
            $error = 'Semua field wajib diisi dengan benar.';
        } else {
            $stmt = $conn->prepare('UPDATE distribusi SET petani=?, alamat=?, jenis_pupuk=?, jumlah=? WHERE id=?');
            $stmt->bind_param('sssii', $petani, $alamat, $jenis, $jumlah, $id);
            if ($stmt->execute()) {
                $success = 'Data berhasil diperbarui.';
            } else {
                $error = 'Gagal memperbarui data.';
            }
            $stmt->close();
        }
    }
}

// =====================
// PENCARIAN & PAGINATION
// =====================
$search   = trim($_GET['q'] ?? '');
$per_page = 10;
$page     = max(1, (int) ($_GET['page'] ?? 1));
$offset   = ($page - 1) * $per_page;

if ($search !== '') {
    $like = "%{$search}%";
    $count_stmt = $conn->prepare('SELECT COUNT(*) FROM distribusi WHERE petani LIKE ? OR alamat LIKE ? OR jenis_pupuk LIKE ?');
    $count_stmt->bind_param('sss', $like, $like, $like);
    $count_stmt->execute();
    $count_stmt->bind_result($total);
    $count_stmt->fetch();
    $count_stmt->close();

    $data_stmt = $conn->prepare('SELECT * FROM distribusi WHERE petani LIKE ? OR alamat LIKE ? OR jenis_pupuk LIKE ? ORDER BY created_at DESC LIMIT ? OFFSET ?');
    $data_stmt->bind_param('sssii', $like, $like, $like, $per_page, $offset);
} else {
    $count_stmt = $conn->prepare('SELECT COUNT(*) FROM distribusi');
    $count_stmt->execute();
    $count_stmt->bind_result($total);
    $count_stmt->fetch();
    $count_stmt->close();

    $data_stmt = $conn->prepare('SELECT * FROM distribusi ORDER BY created_at DESC LIMIT ? OFFSET ?');
    $data_stmt->bind_param('ii', $per_page, $offset);
}

$data_stmt->execute();
$result = $data_stmt->get_result();
$data   = $result->fetch_all(MYSQLI_ASSOC);
$data_stmt->close();

$total_pages = (int) ceil($total / $per_page);
$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - PupukHub</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen">

    <!-- Navbar -->
    <nav class="bg-emerald-800 text-white shadow-md">
        <div class="container mx-auto px-6 py-4 flex justify-between items-center">
            <h1 class="text-xl font-bold tracking-wide">
                PupukHub <span class="font-normal text-emerald-200">| Dashboard</span>
            </h1>
            <div class="flex items-center gap-6">
                <span class="text-sm font-medium bg-emerald-700 px-3 py-1 rounded-full">
                    Halo, <?= htmlspecialchars($_SESSION['user_nama']) ?>
                </span>
                <a href="logout.php" class="text-sm bg-red-500 hover:bg-red-600 px-4 py-1 rounded transition">
                    Keluar
                </a>
            </div>
        </div>
    </nav>

    <main class="container mx-auto px-6 py-10">

        <!-- Notifikasi -->
        <?php if ($error): ?>
            <div class="mb-6 bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg text-sm">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <!-- Form Tambah -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 mb-10">
            <h2 class="text-lg font-bold text-gray-700 mb-4 italic">Tambah Data Distribusi</h2>
            <form method="POST" class="flex flex-wrap gap-4">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="tambah">
                <input type="text" name="petani" placeholder="Nama Petani" required
                    value="<?= htmlspecialchars($_POST['petani'] ?? '') ?>"
                    class="flex-1 min-w-[180px] p-2 border rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none">
                <input type="text" name="alamat" placeholder="Alamat" required
                    value="<?= htmlspecialchars($_POST['alamat'] ?? '') ?>"
                    class="flex-1 min-w-[180px] p-2 border rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none">
                <select name="jenis" class="p-2 border rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none bg-white">
                    <option value="Urea">Urea</option>
                    <option value="NPK">NPK</option>
                    <option value="Organik">Organik</option>
                </select>
                <input type="number" name="jumlah" placeholder="Jumlah (kg)" required min="1" max="99999"
                    value="<?= htmlspecialchars($_POST['jumlah'] ?? '') ?>"
                    class="w-36 p-2 border rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none">
                <button type="submit"
                    class="bg-emerald-600 text-white px-6 py-2 rounded-lg font-bold hover:bg-emerald-700 shadow-md transition">
                    Simpan
                </button>
            </form>
        </div>

        <!-- Search & Info -->
        <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
            <form method="GET" class="flex gap-2">
                <input type="text" name="q" value="<?= htmlspecialchars($search) ?>"
                    placeholder="Cari petani, alamat, jenis..."
                    class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none text-sm w-64">
                <button type="submit" class="bg-emerald-600 text-white px-4 py-2 rounded-lg text-sm font-bold hover:bg-emerald-700 transition">
                    Cari
                </button>
                <?php if ($search): ?>
                    <a href="dashboard.php" class="px-4 py-2 border rounded-lg text-sm text-gray-600 hover:bg-gray-100 transition">Reset</a>
                <?php endif; ?>
            </form>
            <span class="text-sm text-gray-400">
                Total: <strong><?= $total ?></strong> data
                <?= $search ? " · hasil pencarian \"<em>{$search}</em>\"" : '' ?>
            </span>
        </div>

        <!-- Tabel Data -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-5 py-4 text-sm font-bold text-gray-600">No</th>
                        <th class="px-5 py-4 text-sm font-bold text-gray-600">Petani</th>
                        <th class="px-5 py-4 text-sm font-bold text-gray-600">Alamat</th>
                        <th class="px-5 py-4 text-sm font-bold text-gray-600">Jenis</th>
                        <th class="px-5 py-4 text-sm font-bold text-gray-600 text-center">Jumlah</th>
                        <th class="px-5 py-4 text-sm font-bold text-gray-600 text-center">Tanggal</th>
                        <th class="px-5 py-4 text-sm font-bold text-gray-600 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if (empty($data)): ?>
                        <tr>
                            <td colspan="7" class="p-10 text-center text-gray-400">
                                <?= $search ? 'Tidak ada data yang cocok dengan pencarian.' : 'Belum ada data distribusi.' ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($data as $no => $row): ?>
                            <tr class="hover:bg-emerald-50 transition" id="row-<?= $row['id'] ?>">
                                <td class="px-5 py-4 text-gray-500 text-sm"><?= $offset + $no + 1 ?></td>
                                <td class="px-5 py-4 font-medium text-gray-800"><?= htmlspecialchars($row['petani']) ?></td>
                                <td class="px-5 py-4 text-gray-600 text-sm"><?= htmlspecialchars($row['alamat']) ?></td>
                                <td class="px-5 py-4">
                                    <span class="bg-emerald-100 text-emerald-700 px-2 py-1 rounded text-xs font-bold">
                                        <?= htmlspecialchars($row['jenis_pupuk']) ?>
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-center font-mono text-sm"><?= number_format($row['jumlah']) ?> kg</td>
                                <td class="px-5 py-4 text-center text-sm text-gray-500">
                                    <?= date('d/m/Y', strtotime($row['created_at'])) ?>
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <div class="flex justify-center gap-3">
                                        <button onclick="bukaEdit(<?= htmlspecialchars(json_encode($row)) ?>)"
                                            class="text-blue-400 hover:text-blue-600 font-bold text-sm">Edit</button>
                                        <!-- Form Hapus (POST + CSRF) -->
                                        <form method="POST" onsubmit="return confirm('Yakin hapus data ini?')" class="inline">
                                            <?= csrfField() ?>
                                            <input type="hidden" name="action" value="hapus">
                                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                            <button type="submit" class="text-red-400 hover:text-red-600 font-bold text-sm">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="flex justify-center gap-2 mt-6">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?= $i ?><?= $search ? '&q=' . urlencode($search) : '' ?>"
                        class="px-4 py-2 rounded-lg text-sm font-semibold transition
                            <?= $i === $page ? 'bg-emerald-600 text-white' : 'bg-white border text-gray-600 hover:bg-emerald-50' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>

    </main>

    <!-- Modal Edit -->
    <div id="modalEdit" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-lg mx-4 p-6">
            <h3 class="text-lg font-bold text-gray-700 mb-5">Edit Data Distribusi</h3>
            <form method="POST" class="space-y-4">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit-id">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Petani</label>
                    <input type="text" name="petani" id="edit-petani" required
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Alamat</label>
                    <input type="text" name="alamat" id="edit-alamat" required
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none">
                </div>
                <div class="flex gap-4">
                    <div class="flex-1">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Jenis Pupuk</label>
                        <select name="jenis" id="edit-jenis"
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none bg-white">
                            <option value="Urea">Urea</option>
                            <option value="NPK">NPK</option>
                            <option value="Organik">Organik</option>
                        </select>
                    </div>
                    <div class="w-36">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Jumlah (kg)</label>
                        <input type="number" name="jumlah" id="edit-jumlah" required min="1" max="99999"
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none">
                    </div>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="submit"
                        class="flex-1 bg-emerald-600 text-white py-2 rounded-lg font-bold hover:bg-emerald-700 transition">
                        Simpan Perubahan
                    </button>
                    <button type="button" onclick="tutupEdit()"
                        class="flex-1 border border-gray-300 py-2 rounded-lg font-bold text-gray-600 hover:bg-gray-50 transition">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function bukaEdit(row) {
            document.getElementById('edit-id').value     = row.id;
            document.getElementById('edit-petani').value = row.petani;
            document.getElementById('edit-alamat').value = row.alamat;
            document.getElementById('edit-jenis').value  = row.jenis_pupuk;
            document.getElementById('edit-jumlah').value = row.jumlah;
            document.getElementById('modalEdit').classList.remove('hidden');
        }

        function tutupEdit() {
            document.getElementById('modalEdit').classList.add('hidden');
        }

        // Tutup modal jika klik luar
        document.getElementById('modalEdit').addEventListener('click', function(e) {
            if (e.target === this) tutupEdit();
        });
    </script>

</body>
</html>