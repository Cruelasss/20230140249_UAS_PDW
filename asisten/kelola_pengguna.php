<?php
session_start();
require_once '../config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'asisten') {
    header('Location: ../login.php'); exit();
}

$page_title = 'Kelola Pengguna';

// Tambah user
if (isset($_POST['aksi']) && $_POST['aksi']==='tambah') {
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $nama, $email, $password, $role);
    $stmt->execute();
    header('Location: kelola_pengguna.php'); exit();
}

// Edit user
if (isset($_POST['aksi']) && $_POST['aksi']==='edit') {
    $id = (int)$_POST['id'];
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];
    $password = $_POST['password'] ? password_hash($_POST['password'], PASSWORD_DEFAULT) : $_POST['password_lama'];
    $stmt = $conn->prepare("UPDATE users SET nama=?, email=?, password=?, role=? WHERE id=?");
    $stmt->bind_param("ssssi", $nama, $email, $password, $role, $id);
    $stmt->execute();
    header('Location: kelola_pengguna.php'); exit();
}

// Hapus user
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    $conn->query("DELETE FROM users WHERE id=$id");
    header('Location: kelola_pengguna.php'); exit();
}

// Ambil daftar user
$user = [];
$res = $conn->query("SELECT * FROM users ORDER BY role, nama");
while ($row = $res->fetch_assoc()) $user[] = $row;

// Include header template
include 'templates/header.php';
?>

<!-- Daftar Pengguna -->
<div class="bg-white rounded-xl shadow-md overflow-hidden mb-8">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-xl font-bold text-gray-800">Daftar Pengguna</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Nama
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Email
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Role
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Aksi
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($user as $u): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($u['nama']) ?></div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900"><?= htmlspecialchars($u['email']) ?></div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <?php if ($u['role'] === 'asisten'): ?>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                <i class="fas fa-user-tie mr-1"></i>Asisten
                            </span>
                        <?php else: ?>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <i class="fas fa-user-graduate mr-1"></i>Mahasiswa
                            </span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex space-x-2">
                            <button onclick="editUser(<?= $u['id'] ?>, '<?= htmlspecialchars(addslashes($u['nama'])) ?>', '<?= htmlspecialchars(addslashes($u['email'])) ?>', '<?= htmlspecialchars(addslashes($u['role'])) ?>')" class="text-blue-600 hover:text-blue-900">
                                <i class="fas fa-edit mr-1"></i>Edit
                            </button>
                            <a href="kelola_pengguna.php?hapus=<?= $u['id'] ?>" onclick="return confirm('Yakin hapus user ini?')" class="text-red-600 hover:text-red-900">
                                <i class="fas fa-trash mr-1"></i>Hapus
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($user)): ?>
                <tr>
                    <td colspan="4" class="px-6 py-12 text-center">
                        <div class="text-gray-400 text-4xl mb-4">👥</div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Belum ada pengguna</h3>
                        <p class="text-gray-600">Mulai dengan menambahkan pengguna pertama.</p>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Form Tambah Pengguna -->
<div class="bg-white rounded-xl shadow-md p-6">
    <h2 class="text-lg font-bold mb-4 text-gray-800">Tambah Pengguna</h2>
    <form method="post" class="space-y-4">
        <input type="hidden" name="aksi" value="tambah">
        
        <div>
            <label for="nama" class="block text-sm font-medium text-gray-700 mb-2">Nama</label>
            <input type="text" id="nama" name="nama" placeholder="Masukkan nama lengkap" required 
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
            <input type="email" id="email" name="email" placeholder="Masukkan email" required 
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        
        <div>
            <label for="role" class="block text-sm font-medium text-gray-700 mb-2">Role</label>
            <select id="role" name="role" required 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="mahasiswa">Mahasiswa</option>
                <option value="asisten">Asisten</option>
            </select>
        </div>
        
        <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
            <input type="password" id="password" name="password" placeholder="Masukkan password" required 
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        
        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
            <i class="fas fa-plus mr-2"></i>Tambah Pengguna
        </button>
    </form>
</div>

<!-- Edit Modal -->
<div id="editModal" class="fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center hidden z-50">
    <form method="post" class="bg-white p-6 rounded-lg shadow-lg w-96 relative">
        <input type="hidden" name="aksi" value="edit">
        <input type="hidden" name="id" id="edit_id">
        <input type="hidden" name="password_lama" id="edit_password_lama">
        
        <h3 class="text-lg font-medium text-gray-900 mb-4">Edit Pengguna</h3>
        
        <div class="mb-4">
            <label for="edit_nama" class="block text-sm font-medium text-gray-700 mb-2">Nama</label>
            <input type="text" id="edit_nama" name="nama" required 
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        
        <div class="mb-4">
            <label for="edit_email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
            <input type="email" id="edit_email" name="email" required 
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        
        <div class="mb-4">
            <label for="edit_role" class="block text-sm font-medium text-gray-700 mb-2">Role</label>
            <select id="edit_role" name="role" required 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="mahasiswa">Mahasiswa</option>
                <option value="asisten">Asisten</option>
            </select>
        </div>
        
        <div class="mb-4">
            <label for="edit_password" class="block text-sm font-medium text-gray-700 mb-2">Password (isi jika ingin ganti)</label>
            <input type="password" id="edit_password" name="password" placeholder="Password baru" 
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
function editUser(id, nama, email, role) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_nama').value = nama;
    document.getElementById('edit_email').value = email;
    document.getElementById('edit_role').value = role;
    document.getElementById('edit_password_lama').value = '';
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