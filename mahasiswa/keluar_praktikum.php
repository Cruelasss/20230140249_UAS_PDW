<?php
// --- FUNGSI PHP ANDA, TIDAK DIUBAH SAMA SEKALI ---
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$page_title = 'Keluar Praktikum';

// Ambil praktikum yang diikuti mahasiswa
$sql = "SELECT pp.praktikum_id, mp.nama_mk, mp.kode_mk
        FROM pendaftaran_praktikum pp
        JOIN mata_praktikum mp ON pp.praktikum_id = mp.id
        WHERE pp.mahasiswa_id = ?
        ORDER BY mp.nama_mk ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['praktikum_id'])) {
    $praktikum_id = $_POST['praktikum_id'];
    
    // Hapus pendaftaran praktikum
    // Sebaiknya juga tambahkan logika untuk menghapus laporan terkait di sini
    $delete_sql = "DELETE FROM pendaftaran_praktikum WHERE mahasiswa_id = ? AND praktikum_id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("ii", $user_id, $praktikum_id);
    
    if ($delete_stmt->execute()) {
        $_SESSION['success'] = 'Berhasil keluar dari praktikum!';
    } else {
        $_SESSION['error'] = 'Gagal keluar dari praktikum.';
    }
    header('Location: keluar_praktikum.php');
    exit();
}
// --- AKHIR DARI FUNGSI PHP ANDA ---
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> - SIMPRAK</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=Public+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --retro-border: #4d4d4d;
            --retro-teal: #14b8a6;
        }
        body { font-family: 'Public Sans', sans-serif; }
        .font-serif { font-family: 'DM Serif Display', serif; }
        .retro-shadow { box-shadow: 6px 6px 0px var(--retro-border); }
        .sidebar { transition: transform 0.3s ease-in-out; }
        @media (min-width: 768px) { .sidebar { transform: translateX(0); } }
        .sidebar.collapsed { transform: translateX(-100%); }
        .menu-link.active {
            background-color: var(--retro-teal) !important;
            color: white !important;
        }
    </style>
</head>
<body class="bg-amber-50 text-stone-800">
    <div class="flex">
        <div id="sidebar" class="sidebar fixed inset-y-0 left-0 z-50 w-64 bg-amber-900 text-amber-100 transform md:translate-x-0 transition-transform duration-300 ease-in-out border-r-2 border-stone-800">
            <div class="flex items-center justify-between p-4 border-b-2 border-amber-800">
                <h1 class="font-serif text-2xl text-amber-50">SIMPRAK</h1>
            </div>
            <nav class="p-4 flex-grow">
                <ul class="space-y-2">
                    <li><a href="dashboard.php" class="menu-link flex items-center gap-3 p-3 rounded-none transition-colors text-amber-200 hover:bg-amber-800"><i class="fas fa-home w-5 text-center"></i><span class="font-semibold">Dashboard</span></a></li>
                    <li><a href="praktikum_saya.php" class="menu-link flex items-center gap-3 p-3 rounded-none transition-colors text-amber-200 hover:bg-amber-800"><i class="fas fa-book-open w-5 text-center"></i><span class="font-semibold">Praktikum Saya</span></a></li>
                    <li><a href="daftar_praktikum.php" class="menu-link flex items-center gap-3 p-3 rounded-none transition-colors text-amber-200 hover:bg-amber-800"><i class="fas fa-plus-circle w-5 text-center"></i><span class="font-semibold">Daftar Praktikum</span></a></li>
                    <li><a href="detail_praktikum.php" class="menu-link flex items-center gap-3 p-3 rounded-none transition-colors text-amber-200 hover:bg-amber-800"><i class="fas fa-info-circle w-5 text-center"></i><span class="font-semibold">Detail Praktikum</span></a></li>
                    <li><a href="upload_laporan.php" class="menu-link flex items-center gap-3 p-3 rounded-none transition-colors text-amber-200 hover:bg-amber-800"><i class="fas fa-upload w-5 text-center"></i><span class="font-semibold">Upload Laporan</span></a></li>
                    <li><a href="keluar_praktikum.php" class="menu-link active flex items-center gap-3 p-3 rounded-none transition-colors"><i class="fas fa-sign-out-alt w-5 text-center"></i><span class="font-semibold">Keluar Praktikum</span></a></li>
                </ul>
            </nav>
            <div class="p-4 border-t-2 border-amber-800">
                <a href="../logout.php" class="flex items-center gap-3 p-3 rounded-none bg-red-800 hover:bg-red-700 transition-colors text-red-100 hover:text-white">
                    <i class="fas fa-sign-out-alt w-5 text-center"></i><span class="font-semibold">Logout</span>
                </a>
            </div>
        </div>

        <div class="md:ml-64 flex-grow p-6">
            <header class="mb-8">
                <h1 class="font-serif text-4xl font-bold text-amber-900"><?= htmlspecialchars($page_title) ?></h1>
                <p class="text-stone-700 mt-1 text-lg">Pilih praktikum yang ingin Anda tinggalkan.</p>
            </header>

            <?php if (isset($_SESSION['success'])): ?>
            <div class="mb-6 p-4 bg-teal-100 border-2 border-teal-800 text-teal-900 rounded-none retro-shadow">
                <i class="fas fa-check-circle mr-3"></i> <span class="font-bold"><?= htmlspecialchars($_SESSION['success']) ?></span>
            </div>
            <?php unset($_SESSION['success']); endif; ?>

            <div class="bg-white border-2 border-stone-800 p-6 retro-shadow">
                <div class="p-4 mb-6 bg-orange-100 border-2 border-orange-800 text-orange-900 rounded-none">
                    <div class="flex items-start gap-4">
                        <i class="fas fa-exclamation-triangle text-2xl"></i>
                        <div>
                            <h3 class="font-bold text-lg">Peringatan Penting</h3>
                            <p>Keluar dari praktikum adalah tindakan permanen dan akan menghapus semua data pendaftaran Anda. Tindakan ini tidak dapat dibatalkan.</p>
                        </div>
                    </div>
                </div>

                <h2 class="font-serif text-2xl font-bold text-amber-900 mb-4">Praktikum yang Anda Ikuti</h2>
                
                <?php if ($result && $result->num_rows > 0): ?>
                    <div class="space-y-4">
                        <?php while($praktikum = $result->fetch_assoc()): ?>
                            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 p-4 border-2 border-stone-800 bg-amber-50">
                                <div>
                                    <h3 class="font-bold text-lg text-stone-800"><?= htmlspecialchars($praktikum['nama_mk']) ?></h3>
                                    <p class="text-sm text-stone-600"><?= htmlspecialchars($praktikum['kode_mk']) ?></p>
                                </div>
                                <form method="post" class="w-full sm:w-auto">
                                    <input type="hidden" name="praktikum_id" value="<?= $praktikum['praktikum_id'] ?>">
                                    <button type="submit" class="w-full bg-red-600 text-white font-bold p-3 border-2 border-stone-800 hover:bg-red-700 transition-colors" onclick="return confirm('Anda yakin ingin keluar dari praktikum \'<?= htmlspecialchars($praktikum['nama_mk']) ?>\'? Tindakan ini tidak dapat dibatalkan.')">
                                        <i class="fas fa-times-circle mr-2"></i>KELUAR
                                    </button>
                                </form>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center p-8 border-2 border-dashed border-stone-300">
                        <i class="fas fa-check-circle text-6xl text-stone-400 mb-4"></i>
                        <h3 class="font-serif text-2xl font-bold text-amber-900">Aman!</h3>
                        <p class="text-stone-600">Anda saat ini tidak terdaftar di praktikum mana pun.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>