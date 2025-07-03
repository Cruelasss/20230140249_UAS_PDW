<?php
// --- FUNGSI PHP ANDA, TIDAK DIUBAH SAMA SEKALI ---
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$page_title = 'Detail Praktikum';

// Ambil detail praktikum yang diikuti mahasiswa
$sql = "SELECT mp.*, u.nama as nama_asisten, COUNT(m.id) as jumlah_modul
        FROM mata_praktikum mp 
        LEFT JOIN users u ON mp.asisten_id = u.id 
        LEFT JOIN modul m ON mp.id = m.praktikum_id
        INNER JOIN pendaftaran_praktikum pp ON mp.id = pp.praktikum_id
        WHERE pp.mahasiswa_id = ?
        GROUP BY mp.id
        ORDER BY mp.nama_mk ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
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
                    <li><a href="detail_praktikum.php" class="menu-link active flex items-center gap-3 p-3 rounded-none transition-colors"><i class="fas fa-info-circle w-5 text-center"></i><span class="font-semibold">Detail Praktikum</span></a></li>
                    <li><a href="upload_laporan.php" class="menu-link flex items-center gap-3 p-3 rounded-none transition-colors text-amber-200 hover:bg-amber-800"><i class="fas fa-upload w-5 text-center"></i><span class="font-semibold">Upload Laporan</span></a></li>
                    <li><a href="keluar_praktikum.php" class="menu-link flex items-center gap-3 p-3 rounded-none transition-colors text-amber-200 hover:bg-amber-800"><i class="fas fa-sign-out-alt w-5 text-center"></i><span class="font-semibold">Keluar Praktikum</span></a></li>
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
                <p class="text-stone-700 mt-1 text-lg">Informasi lengkap mengenai praktikum yang Anda ikuti.</p>
            </header>

            <?php if ($result && $result->num_rows > 0): ?>
                <div class="space-y-8">
                    <?php while($praktikum = $result->fetch_assoc()): ?>
                        <div class="bg-white border-2 border-stone-800 retro-shadow">
                            <div class="p-6 border-b-2 border-stone-800 flex items-center gap-4">
                                <div class="w-12 h-12 flex items-center justify-center bg-yellow-400 border-2 border-stone-800">
                                    <i class="fas fa-flask text-2xl text-stone-800"></i>
                                </div>
                                <div>
                                    <h3 class="font-serif text-2xl font-bold text-amber-900"><?= htmlspecialchars($praktikum['nama_mk']) ?></h3>
                                    <p class="text-stone-600 font-semibold"><?= htmlspecialchars($praktikum['kode_mk']) ?></p>
                                </div>
                            </div>
                            
                            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4">
                                <div class="info-item">
                                    <p class="text-sm font-bold uppercase text-stone-600">Jumlah Modul</p>
                                    <p class="text-lg font-semibold"><?= $praktikum['jumlah_modul'] ?> Modul</p>
                                </div>
                                <div class="info-item">
                                    <p class="text-sm font-bold uppercase text-stone-600">Asisten Dosen</p>
                                    <p class="text-lg font-semibold"><?= htmlspecialchars($praktikum['nama_asisten'] ?: 'N/A') ?></p>
                                </div>
                                <div class="info-item">
                                    <p class="text-sm font-bold uppercase text-stone-600">Semester</p>
                                    <p class="text-lg font-semibold"><?= htmlspecialchars($praktikum['semester'] ?: '-') ?></p>
                                </div>
                            </div>

                             <div class="px-6 pb-6">
                                <p class="text-sm font-bold uppercase text-stone-600">Deskripsi</p>
                                <p class="text-stone-700 mt-1"><?= htmlspecialchars($praktikum['deskripsi'] ?: 'Deskripsi belum tersedia.') ?></p>
                            </div>

                            <?php
                            $sql_modul = "SELECT * FROM modul WHERE praktikum_id = ? ORDER BY id ASC";
                            $stmt_modul = $conn->prepare($sql_modul);
                            $stmt_modul->bind_param("i", $praktikum['id']);
                            $stmt_modul->execute();
                            $modul_result = $stmt_modul->get_result();
                            if ($modul_result->num_rows > 0):
                            ?>
                                <div class="border-t-2 border-stone-800 p-6">
                                    <h4 class="font-serif text-xl font-bold text-amber-900 mb-4">Daftar Modul</h4>
                                    <div class="space-y-3">
                                        <?php while($modul = $modul_result->fetch_assoc()): ?>
                                            <div class="pb-3 border-b-2 border-dashed border-stone-200 last:border-b-0 last:pb-0">
                                                <p class="font-bold text-stone-800"><?= htmlspecialchars($modul['judul_modul']) ?></p>
                                                <p class="text-sm text-stone-600"><?= htmlspecialchars($modul['deskripsi_modul'] ?: 'Tidak ada deskripsi.') ?></p>
                                            </div>
                                        <?php endwhile; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="bg-white border-2 border-stone-800 p-8 text-center retro-shadow">
                    <i class="fas fa-folder-open text-6xl text-stone-400 mb-4"></i>
                    <h3 class="font-serif text-2xl font-bold text-amber-900">Anda Belum Mengikuti Praktikum</h3>
                    <p class="text-stone-600">Daftar praktikum terlebih dahulu untuk melihat detail lengkapnya di sini.</p>
                    <div class="mt-6">
                        <a href="daftar_praktikum.php" class="inline-block bg-teal-500 text-white font-bold p-3 border-2 border-stone-800 hover:bg-teal-600">
                            Daftar Praktikum Sekarang
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>