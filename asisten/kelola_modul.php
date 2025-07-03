<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'asisten') {
    header('Location: ../login.php');
    exit();
}

$page_title = 'Kelola Modul';

// Ambil daftar praktikum untuk filter/tambah modul
$praktikum = [];
$res = $conn->query("SELECT id, nama_mk FROM mata_praktikum ORDER BY nama_mk ASC");
while ($row = $res->fetch_assoc()) $praktikum[] = $row;

// Filter praktikum
$praktikum_id = isset($_GET['praktikum_id']) ? (int)$_GET['praktikum_id'] : ($praktikum[0]['id'] ?? 0);

// Tambah modul
if (isset($_POST['aksi']) && $_POST['aksi'] === 'tambah') {
    $judul = trim($_POST['judul_modul']);
    $deskripsi = trim($_POST['deskripsi_modul']);
    $pid = (int)$_POST['praktikum_id'];
    $file = $_FILES['file_materi'];
    $file_name = null;
    if ($file['name']) {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['pdf','docx','doc'])) {
            $file_name = uniqid('materi_').'.'.$ext;
            move_uploaded_file($file['tmp_name'], "../uploads/materi/".$file_name);
        }
    }
    $stmt = $conn->prepare("INSERT INTO modul (praktikum_id, judul_modul, deskripsi_modul, file_materi) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $pid, $judul, $deskripsi, $file_name);
    $stmt->execute();
    header("Location: kelola_modul.php?praktikum_id=$pid"); exit();
}

// Edit modul
if (isset($_POST['aksi']) && $_POST['aksi'] === 'edit') {
    $id = (int)$_POST['id'];
    $judul = trim($_POST['judul_modul']);
    $deskripsi = trim($_POST['deskripsi_modul']);
    $pid = (int)$_POST['praktikum_id'];
    $file = $_FILES['file_materi'];
    $file_name = $_POST['file_materi_lama'];
    if ($file['name']) {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['pdf','docx','doc'])) {
            $file_name = uniqid('materi_').'.'.$ext;
            move_uploaded_file($file['tmp_name'], "../uploads/materi/".$file_name);
        }
    }
    $stmt = $conn->prepare("UPDATE modul SET judul_modul=?, deskripsi_modul=?, file_materi=? WHERE id=?");
    $stmt->bind_param("sssi", $judul, $deskripsi, $file_name, $id);
    $stmt->execute();
    header("Location: kelola_modul.php?praktikum_id=$pid"); exit();
}

// Hapus modul
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    $pid = (int)$_GET['praktikum_id'];
    $conn->query("DELETE FROM modul WHERE id=$id");
    header("Location: kelola_modul.php?praktikum_id=$pid"); exit();
}

// Ambil daftar modul untuk praktikum terpilih
$modul = [];
if ($praktikum_id) {
    $res = $conn->query("SELECT * FROM modul WHERE praktikum_id=$praktikum_id ORDER BY id ASC");
    while ($row = $res->fetch_assoc()) $modul[] = $row;
}

// Include header template
include 'templates/header.php';
?>

<!-- Filter Praktikum -->
<form method="get" class="mb-6 flex gap-2 items-center">
    <label class="font-medium text-gray-700">Pilih Praktikum:</label>
    <select name="praktikum_id" onchange="this.form.submit()" class="border rounded px-3 py-2 bg-white">
        <?php foreach ($praktikum as $p): ?>
            <option value="<?= $p['id'] ?>" <?= $praktikum_id==$p['id']?'selected':'' ?>><?= htmlspecialchars($p['nama_mk']) ?></option>
        <?php endforeach; ?>
    </select>
</form>

<!-- Daftar Modul -->
<div class="bg-white rounded-xl shadow-md overflow-hidden mb-8">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-xl font-bold text-gray-800">Daftar Modul</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Judul Modul
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Deskripsi
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        File Materi
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Aksi
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($modul as $m): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($m['judul_modul']) ?></div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-gray-900 max-w-xs truncate"><?= htmlspecialchars($m['deskripsi_modul']) ?></div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <?php if ($m['file_materi']): ?>
                            <a href="../uploads/materi/<?= urlencode($m['file_materi']) ?>" target="_blank" class="text-blue-600 hover:text-blue-800">
                                <i class="fas fa-download mr-1"></i>Download
                            </a>
                        <?php else: ?>
                            <span class="text-gray-400">-</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex space-x-2">
                            <button onclick="editModul(<?= $m['id'] ?>, '<?= htmlspecialchars(addslashes($m['judul_modul'])) ?>', '<?= htmlspecialchars(addslashes($m['deskripsi_modul'])) ?>', '<?= htmlspecialchars(addslashes($m['file_materi'])) ?>')" class="text-blue-600 hover:text-blue-900">
                                <i class="fas fa-edit mr-1"></i>Edit
                            </button>
                            <a href="kelola_modul.php?praktikum_id=<?= $praktikum_id ?>&hapus=<?= $m['id'] ?>" onclick="return confirm('Yakin hapus modul ini?')" class="text-red-600 hover:text-red-900">
                                <i class="fas fa-trash mr-1"></i>Hapus
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($modul)): ?>
                <tr>
                    <td colspan="4" class="px-6 py-12 text-center">
                        <div class="text-gray-400 text-4xl mb-4">📚</div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Belum ada modul</h3>
                        <p class="text-gray-600">Mulai dengan menambahkan modul pertama.</p>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Form Tambah Modul -->
<div class="bg-white rounded-xl shadow-md p-6">
    <h2 class="text-lg font-bold mb-4 text-gray-800">Tambah Modul</h2>
    <form method="post" enctype="multipart/form-data" class="space-y-4">
        <input type="hidden" name="aksi" value="tambah">
        <input type="hidden" name="praktikum_id" value="<?= $praktikum_id ?>">
        
        <div>
            <label for="judul_modul" class="block text-sm font-medium text-gray-700 mb-2">Judul Modul</label>
            <input type="text" id="judul_modul" name="judul_modul" placeholder="Masukkan judul modul" required 
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        
        <div>
            <label for="deskripsi_modul" class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
            <textarea id="deskripsi_modul" name="deskripsi_modul" placeholder="Masukkan deskripsi modul" required rows="3"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
        </div>
        
        <div>
            <label for="file_materi" class="block text-sm font-medium text-gray-700 mb-2">File Materi (PDF, DOC, DOCX)</label>
            <input type="file" id="file_materi" name="file_materi" accept=".pdf,.doc,.docx" 
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        
        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
            <i class="fas fa-plus mr-2"></i>Tambah Modul
        </button>
    </form>
</div>

<!-- Edit Modal -->
<div id="editModal" class="fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center hidden z-50">
    <form method="post" enctype="multipart/form-data" class="bg-white p-6 rounded-lg shadow-lg w-96 relative">
        <input type="hidden" name="aksi" value="edit">
        <input type="hidden" name="id" id="edit_id">
        <input type="hidden" name="praktikum_id" value="<?= $praktikum_id ?>">
        <input type="hidden" name="file_materi_lama" id="edit_file_materi_lama">
        
        <h3 class="text-lg font-medium text-gray-900 mb-4">Edit Modul</h3>
        
        <div class="mb-4">
            <label for="edit_judul_modul" class="block text-sm font-medium text-gray-700 mb-2">Judul Modul</label>
            <input type="text" id="edit_judul_modul" name="judul_modul" required 
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        
        <div class="mb-4">
            <label for="edit_deskripsi_modul" class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
            <textarea id="edit_deskripsi_modul" name="deskripsi_modul" required rows="3"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
        </div>
        
        <div class="mb-4">
            <label for="edit_file_materi" class="block text-sm font-medium text-gray-700 mb-2">File Materi (upload baru untuk ganti)</label>
            <input type="file" id="edit_file_materi" name="file_materi" accept=".pdf,.doc,.docx" 
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        
        <div class="flex gap-2 mt-6">
            <button type="button" onclick="closeEdit()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                Batal
            </button>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                Simpan
            </button>
        </div>
    </form>
</div>

<script>
function editModul(id, judul, desk, file) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_judul_modul').value = judul;
    document.getElementById('edit_deskripsi_modul').value = desk;
    document.getElementById('edit_file_materi_lama').value = file;
    document.getElementById('editModal').classList.remove('hidden');
}

function closeEdit() {
    document.getElementById('editModal').classList.add('hidden');
}
</script>

<?php
// Include footer template
include 'templates/footer.php';
?> 