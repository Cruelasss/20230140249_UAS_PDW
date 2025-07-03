<?php
session_start();
require_once 'config.php';

$page_title = 'Katalog Praktikum';

// --- LOGIKA PHP ANDA (TIDAK DIUBAH) ---
$sql = "SELECT mp.*, u.nama as nama_asisten,
        (SELECT COUNT(*) FROM pendaftaran_praktikum pp WHERE pp.praktikum_id = mp.id) as jumlah_mahasiswa,
        (SELECT COUNT(*) FROM modul m WHERE m.praktikum_id = mp.id) as jumlah_modul
        FROM mata_praktikum mp 
        LEFT JOIN users u ON mp.asisten_id = u.id 
        ORDER BY mp.nama_mk ASC";
$result = $conn->query($sql);

$user_registered = [];
if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'mahasiswa') {
    $user_id = $_SESSION['user_id'];
    $check_sql = "SELECT praktikum_id FROM pendaftaran_praktikum WHERE mahasiswa_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    while ($row = $check_result->fetch_assoc()) {
        $user_registered[] = $row['praktikum_id'];
    }
}
// --- AKHIR LOGIKA PHP ---
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> - SIMPRAK</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=Public+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --retro-border: #4d4d4d;
        }
        body {
            font-family: 'Public Sans', sans-serif;
        }
        .font-serif {
            font-family: 'DM Serif Display', serif;
        }
        .retro-shadow {
            box-shadow: 6px 6px 0px var(--retro-border);
        }
    </style>
</head>
<body class="bg-amber-50 text-stone-800">
    <header class="bg-amber-100 border-b-2 border-stone-800 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center">
                    <h1 class="font-serif text-3xl font-bold text-amber-900">SIMPRAK</h1>
                </div>
                <nav class="hidden md:flex items-center space-x-6 text-sm font-bold uppercase tracking-wider">
                    <a href="index.php" class="text-stone-700 hover:text-amber-900">Beranda</a>
                    <a href="katalog.php" class="text-amber-900 border-b-2 border-amber-900">Katalog</a>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="<?= $_SESSION['role'] === 'asisten' ? 'asisten/dashboard.php' : 'mahasiswa/dashboard.php' ?>" class="text-stone-700 hover:text-amber-900">Dashboard</a>
                        <a href="logout.php" class="text-stone-700 hover:text-amber-900">Logout</a>
                    <?php else: ?>
                        <a href="login.php" class="bg-stone-800 text-white px-4 py-2 hover:bg-stone-700">Login</a>
                    <?php endif; ?>
                </nav>
                </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="mb-10 text-center">
            <h1 class="font-serif text-5xl font-bold text-amber-900 mb-2">Katalog Praktikum</h1>
            <p class="text-lg text-stone-700">Jelajahi semua mata kuliah praktikum yang tersedia di SIMPRAK.</p>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="mb-6 p-4 bg-teal-100 border-2 border-teal-800 text-teal-900 rounded-none retro-shadow">
                <i class="fas fa-check-circle mr-3"></i> <span class="font-bold"><?= htmlspecialchars($_SESSION['success']) ?></span>
            </div>
            <?php unset($_SESSION['success']); endif; ?>

        <?php if ($result->num_rows > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php while($row = $result->fetch_assoc()): ?>
                    <div class="bg-white border-2 border-stone-800 flex flex-col retro-shadow transition-all duration-200 hover:transform hover:-translate-y-1 hover:-translate-x-1 hover:shadow-[10px_10px_0px_var(--retro-border)]">
                        <div class="p-6 flex-grow">
                            <div class="mb-4">
                                <span class="font-bold bg-yellow-400 border-2 border-stone-800 text-stone-800 px-3 py-1 text-sm">
                                    <?= htmlspecialchars($row['kode_mk']) ?>
                                </span>
                            </div>
                            
                            <h3 class="font-serif text-2xl font-bold text-amber-900 mb-2">
                                <?= htmlspecialchars($row['nama_mk']) ?>
                            </h3>
                            
                            <p class="text-stone-600 text-sm mb-4 h-20 overflow-hidden">
                                <?= htmlspecialchars($row['deskripsi'] ?: 'Tidak ada deskripsi yang tersedia untuk praktikum ini.') ?>
                            </p>
                            
                            <div class="space-y-2 text-sm text-stone-700 pt-4 border-t-2 border-dashed border-stone-300">
                                <p><i class="fas fa-user-tie w-5 mr-1"></i> Asisten: <strong><?= htmlspecialchars($row['nama_asisten'] ?: 'N/A') ?></strong></p>
                                <p><i class="fas fa-users w-5 mr-1"></i> Peserta: <strong><?= $row['jumlah_mahasiswa'] ?> Mahasiswa</strong></p>
                                <p><i class="fas fa-book w-5 mr-1"></i> Materi: <strong><?= $row['jumlah_modul'] ?> Modul</strong></p>
                            </div>
                        </div>
                        
                        <div class="p-4 bg-amber-50 border-t-2 border-stone-800">
                            <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'mahasiswa'): ?>
                                <?php if (in_array($row['id'], $user_registered)): ?>
                                    <a href="mahasiswa/praktikum_saya.php" class="block w-full text-center p-3 font-bold bg-yellow-400 text-stone-800 border-2 border-stone-800">
                                        <i class="fas fa-check-circle"></i> SUDAH TERDAFTAR
                                    </a>
                                <?php else: ?>
                                    <a href="mahasiswa/daftar_praktikum.php" class="block w-full text-center p-3 font-bold bg-teal-500 text-white border-2 border-stone-800 hover:bg-teal-600 transition-colors">
                                        <i class="fas fa-plus-circle"></i> DAFTAR SEKARANG
                                    </a>
                                <?php endif; ?>
                            <?php else: ?>
                                <a href="login.php" class="block w-full text-center p-3 font-bold bg-stone-800 text-white border-2 border-stone-800 hover:bg-stone-700 transition-colors">
                                    <i class="fas fa-sign-in-alt"></i> LOGIN UNTUK DAFTAR
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="bg-white border-2 border-stone-800 p-12 text-center retro-shadow">
                <i class="fas fa-box-open text-7xl text-stone-400 mb-4"></i>
                <h3 class="font-serif text-3xl font-bold text-amber-900">Belum Ada Praktikum</h3>
                <p class="text-stone-600 text-lg">Saat ini belum ada mata praktikum yang tersedia. Silakan cek kembali nanti.</p>
            </div>
        <?php endif; ?>
    </main>

    <footer class="bg-amber-100 border-t-2 border-stone-800 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <p class="text-center text-stone-700 font-semibold">&copy; <?= date('Y') ?> SIMPRAK - Portal Praktikum Bergaya Retro</p>
        </div>
    </footer>
</body>
</html>