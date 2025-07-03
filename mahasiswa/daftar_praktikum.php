<?php
// --- FUNGSI PHP ANDA, TIDAK DIUBAH SAMA SEKALI ---
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$page_title = 'Daftar Praktikum';

// Ambil semua praktikum yang tersedia
$sql = "SELECT mp.*, u.nama as nama_asisten,
        (SELECT COUNT(*) FROM pendaftaran_praktikum pp WHERE pp.praktikum_id = mp.id) as jumlah_mahasiswa,
        (SELECT COUNT(*) FROM modul m WHERE m.praktikum_id = mp.id) as jumlah_modul
        FROM mata_praktikum mp 
        LEFT JOIN users u ON mp.asisten_id = u.id 
        ORDER BY mp.nama_mk ASC";
$result = $conn->query($sql);

// Ambil praktikum yang sudah diikuti mahasiswa
$sql_enrolled = "SELECT praktikum_id FROM pendaftaran_praktikum WHERE mahasiswa_id = ?";
$stmt_enrolled = $conn->prepare($sql_enrolled);
$stmt_enrolled->bind_param("i", $user_id);
$stmt_enrolled->execute();
$enrolled_result = $stmt_enrolled->get_result();
$enrolled_praktikum = [];
while($row = $enrolled_result->fetch_assoc()) {
    $enrolled_praktikum[] = $row['praktikum_id'];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['praktikum_id'])) {
    $praktikum_id_to_enroll = $_POST['praktikum_id'];
    
    $check_sql = "SELECT id FROM pendaftaran_praktikum WHERE mahasiswa_id = ? AND praktikum_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ii", $user_id, $praktikum_id_to_enroll);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows == 0) {
        $enroll_sql = "INSERT INTO pendaftaran_praktikum (mahasiswa_id, praktikum_id) VALUES (?, ?)";
        $enroll_stmt = $conn->prepare($enroll_sql);
        $enroll_stmt->bind_param("ii", $user_id, $praktikum_id_to_enroll);
        
        if ($enroll_stmt->execute()) {
            echo "<script>alert('Berhasil mendaftar praktikum!'); window.location.href='daftar_praktikum.php';</script>";
        } else {
            echo "<script>alert('Gagal mendaftar praktikum. Silakan coba lagi.');</script>";
        }
    } else {
        echo "<script>alert('Anda sudah terdaftar di praktikum ini.');</script>";
    }
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
            --sidebar-bg: #362314;
            --sidebar-text: #eaddc7;
            --sidebar-active: #c69c6d;
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
        .sidebar {
            transition: transform 0.3s ease-in-out;
        }
        @media (min-width: 768px) {
            .sidebar { transform: translateX(0); }
        }
        .sidebar.collapsed {
            transform: translateX(-100%);
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
                    <li><a href="dashboard.php" class="flex items-center gap-3 p-3 rounded-none transition-colors text-amber-200 hover:bg-amber-800"><i class="fas fa-home w-5 text-center"></i><span class="font-semibold">Dashboard</span></a></li>
                    <li><a href="praktikum_saya.php" class="flex items-center gap-3 p-3 rounded-none transition-colors text-amber-200 hover:bg-amber-800"><i class="fas fa-book-open w-5 text-center"></i><span class="font-semibold">Praktikum Saya</span></a></li>
                    <li><a href="daftar_praktikum.php" class="flex items-center gap-3 p-3 rounded-none transition-colors bg-amber-800 text-white"><i class="fas fa-plus-circle w-5 text-center"></i><span class="font-semibold">Daftar Praktikum</span></a></li>
                    <li><a href="upload_laporan.php" class="flex items-center gap-3 p-3 rounded-none transition-colors text-amber-200 hover:bg-amber-800"><i class="fas fa-upload w-5 text-center"></i><span class="font-semibold">Upload Laporan</span></a></li>
                  <li><a href="detail_pratikum.php" class="flex items-center gap-3 p-3 rounded-none transition-colors text-amber-200 hover:bg-amber-800"><i class="fas fa-info w-5 text-center"></i><span class="font-semibold">Detail Pratikum</span></a></li>
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
                <p class="text-stone-700 mt-1 text-lg">Pilih dan daftar untuk mata kuliah praktikum yang tersedia.</p>
            </header>

            <?php if ($result && $result->num_rows > 0): ?>
                <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-8">
                    <?php while($praktikum = $result->fetch_assoc()): ?>
                        <div class="bg-white border-2 border-stone-800 flex flex-col retro-shadow transition-all duration-200 hover:transform hover:-translate-y-1 hover:-translate-x-1 hover:shadow-[10px_10px_0px_var(--retro-border)]">
                            <div class="p-6 flex-grow">
                                <h3 class="font-serif text-2xl font-bold text-amber-900"><?= htmlspecialchars($praktikum['nama_mk']) ?></h3>
                                <p class="text-stone-600 mb-4 font-semibold"><?= htmlspecialchars($praktikum['kode_mk']) ?></p>
                                
                                <div class="flex flex-wrap gap-2 text-sm mt-4 pt-4 border-t-2 border-dashed border-stone-300">
                                    <span class="bg-amber-100 border border-amber-300 text-amber-800 font-semibold px-3 py-1"><?= $praktikum['jumlah_modul'] ?> Modul</span>
                                    <span class="bg-amber-100 border border-amber-300 text-amber-800 font-semibold px-3 py-1">Asisten: <?= htmlspecialchars($praktikum['nama_asisten'] ?? 'N/A') ?></span>
                                </div>
                            </div>
                            
                            <div class="mt-auto p-4 bg-amber-100 border-t-2 border-stone-800">
                                <?php if (in_array($praktikum['id'], $enrolled_praktikum)): ?>
                                    <div class="w-full text-center p-3 font-bold bg-yellow-400 border-2 border-stone-800">
                                        <i class="fas fa-check-circle"></i> TERDAFTAR
                                    </div>
                                <?php else: ?>
                                    <form method="post" action="daftar_praktikum.php" class="w-full">
                                        <input type="hidden" name="praktikum_id" value="<?= $praktikum['id'] ?>">
                                        <button type="submit" class="w-full text-center p-3 font-bold bg-teal-500 text-white border-2 border-stone-800 hover:bg-teal-600 transition-colors">
                                            <i class="fas fa-plus-circle"></i> DAFTAR SEKARANG
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="bg-white border-2 border-stone-800 p-8 text-center retro-shadow">
                    <i class="fas fa-box-open text-6xl text-stone-400 mb-4"></i>
                    <h3 class="font-serif text-2xl font-bold text-amber-900">Tidak Ada Praktikum Tersedia</h3>
                    <p class="text-stone-600">Saat ini belum ada praktikum yang dibuka untuk pendaftaran.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>