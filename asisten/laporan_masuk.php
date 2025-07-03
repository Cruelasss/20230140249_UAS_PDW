<?php
session_start();
require_once '../config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'asisten') {
    header('Location: ../login.php'); exit();
}

$page_title = 'Laporan Masuk';

// Ambil daftar praktikum
$praktikum = [];
$res = $conn->query("SELECT id, nama_mk FROM mata_praktikum ORDER BY nama_mk ASC");
while ($row = $res->fetch_assoc()) $praktikum[] = $row;
$praktikum_id = isset($_GET['praktikum_id']) ? (int)$_GET['praktikum_id'] : ($praktikum[0]['id'] ?? 0);

// Ambil daftar modul untuk praktikum terpilih
$modul = [];
if ($praktikum_id) {
    $res = $conn->query("SELECT id, judul_modul FROM modul WHERE praktikum_id=$praktikum_id ORDER BY id ASC");
    while ($row = $res->fetch_assoc()) $modul[] = $row;
}
$modul_id = isset($_GET['modul_id']) ? (int)$_GET['modul_id'] : 0;

// Proses penilaian
if (isset($_POST['aksi']) && $_POST['aksi']==='nilai') {
    $id = (int)$_POST['id'];
    $nilai = (int)$_POST['nilai'];
    $feedback = trim($_POST['feedback']);
    $conn->query("UPDATE laporan SET nilai=$nilai, feedback='".$conn->real_escape_string($feedback)."' WHERE id=$id");
    header("Location: laporan_masuk.php?praktikum_id=$praktikum_id&modul_id=$modul_id"); exit();
}

// Ambil daftar laporan
$sql = "SELECT l.*, m.judul_modul, mp.nama_mk, u.nama as nama_mhs, u.email FROM laporan l JOIN modul m ON l.modul_id=m.id JOIN users u ON l.mahasiswa_id=u.id JOIN mata_praktikum mp ON m.praktikum_id=mp.id";
if ($praktikum_id) $sql .= " WHERE mp.id=$praktikum_id";
if ($modul_id) $sql .= ($praktikum_id ? " AND" : " WHERE") . " m.id=$modul_id";
$sql .= " ORDER BY l.tgl_kumpul DESC";
$laporan = [];
$res = $conn->query($sql);
while ($row = $res->fetch_assoc()) $laporan[] = $row;

// Include header template
include 'templates/header.php';
?>

<!-- Filter Section -->
<div class="bg-white rounded-xl shadow-md p-6 mb-6">
    <form method="get" class="flex gap-4 items-center">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Praktikum:</label>
            <select name="praktikum_id" onchange="this.form.submit()" class="border rounded px-3 py-2 bg-white">
                <?php foreach ($praktikum as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= $praktikum_id==$p['id']?'selected':'' ?>><?= htmlspecialchars($p['nama_mk']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Modul:</label>
            <select name="modul_id" onchange="this.form.submit()" class="border rounded px-3 py-2 bg-white">
                <option value="0">Semua</option>
                <?php foreach ($modul as $m): ?>
                    <option value="<?= $m['id'] ?>" <?= $modul_id==$m['id']?'selected':'' ?>><?= htmlspecialchars($m['judul_modul']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </form>
</div>

<!-- Laporan Table -->
<div class="bg-white rounded-xl shadow-md overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-xl font-bold text-gray-800">Daftar Laporan</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Mahasiswa
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Praktikum
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Modul
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        File
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Nilai
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Feedback
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Aksi
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($laporan as $l): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($l['nama_mhs']) ?></div>
                        <div class="text-sm text-gray-500"><?= htmlspecialchars($l['email']) ?></div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900"><?= htmlspecialchars($l['nama_mk']) ?></div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900"><?= htmlspecialchars($l['judul_modul']) ?></div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <a href="../uploads/laporan/<?= urlencode($l['file_laporan']) ?>" target="_blank" class="text-blue-600 hover:text-blue-800">
                            <i class="fas fa-download mr-1"></i>Download
                        </a>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <?php if ($l['nilai'] !== null): ?>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <?= $l['nilai'] ?>
                            </span>
                        <?php else: ?>
                            <span class="text-gray-400">-</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-gray-900 max-w-xs truncate">
                            <?= htmlspecialchars($l['feedback'] ?: '-') ?>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <button onclick="nilaiLaporan(<?= $l['id'] ?>, <?= (int)$l['nilai'] ?>, '<?= htmlspecialchars(addslashes($l['feedback'])) ?>')" class="text-blue-600 hover:text-blue-900">
                            <i class="fas fa-star mr-1"></i>Nilai
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($laporan)): ?>
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center">
                        <div class="text-gray-400 text-4xl mb-4">📄</div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Belum ada laporan</h3>
                        <p class="text-gray-600">Laporan yang masuk akan muncul di sini.</p>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Penilaian -->
<div id="nilaiModal" class="fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center hidden z-50">
    <form method="post" class="bg-white p-6 rounded-lg shadow-lg w-96 relative">
        <input type="hidden" name="aksi" value="nilai">
        <input type="hidden" name="id" id="nilai_id">
        
        <h3 class="text-lg font-medium text-gray-900 mb-4">Nilai Laporan</h3>
        
        <div class="mb-4">
            <label for="nilai_nilai" class="block text-sm font-medium text-gray-700 mb-2">Nilai (0-100)</label>
            <input type="number" name="nilai" id="nilai_nilai" min="0" max="100" required 
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        
        <div class="mb-4">
            <label for="nilai_feedback" class="block text-sm font-medium text-gray-700 mb-2">Feedback</label>
            <textarea name="feedback" id="nilai_feedback" rows="3"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
        </div>
        
        <div class="flex gap-2 mt-6">
            <button type="button" onclick="closeNilai()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                Batal
            </button>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                Simpan
            </button>
        </div>
    </form>
</div>

<script>
function nilaiLaporan(id, nilai, feedback) {
    document.getElementById('nilai_id').value = id;
    document.getElementById('nilai_nilai').value = nilai || '';
    document.getElementById('nilai_feedback').value = feedback || '';
    document.getElementById('nilaiModal').classList.remove('hidden');
}

function closeNilai() {
    document.getElementById('nilaiModal').classList.add('hidden');
}
</script>

<?php
// Include footer template
include 'templates/footer.php';
?> 