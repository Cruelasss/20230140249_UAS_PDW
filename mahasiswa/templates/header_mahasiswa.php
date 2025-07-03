<?php
// Cek sesi (diasumsikan session_start() dan require_once '../config.php' sudah ada di file utama)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header('Location: ../login.php');
    exit();
}

// Ambil data user yang sedang login untuk ditampilkan di sidebar
$user_id = $_SESSION['user_id'];
$user_query = "SELECT nama, email FROM users WHERE id = ?";
$stmt_user = $conn->prepare($user_query);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$user_data = $stmt_user->get_result()->fetch_assoc();

// Hitung statistik singkat untuk sidebar
$praktikum_count_query = "SELECT COUNT(*) as total FROM pendaftaran_praktikum WHERE mahasiswa_id = ?";
$stmt_praktikum = $conn->prepare($praktikum_count_query);
$stmt_praktikum->bind_param("i", $user_id);
$stmt_praktikum->execute();
$praktikum_count = $stmt_praktikum->get_result()->fetch_assoc()['total'];

$laporan_count_query = "SELECT COUNT(*) as total FROM laporan WHERE mahasiswa_id = ?";
$stmt_laporan = $conn->prepare($laporan_count_query);
$stmt_laporan->bind_param("i", $user_id);
$stmt_laporan->execute();
$laporan_count = $stmt_laporan->get_result()->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? htmlspecialchars($page_title) . ' - SIMPRAK' : 'SIMPRAK - Portal Mahasiswa' ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=Public+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --retro-border: #4d4d4d;
            --retro-teal: #14b8a6; /* Warna untuk menu aktif */
        }
        body {
            font-family: 'Public Sans', sans-serif;
        }
        .font-serif {
            font-family: 'DM Serif Display', serif;
        }
        .retro-shadow {
            box-shadow: 4px 4px 0px var(--retro-border);
        }
        .sidebar {
            transition: transform 0.3s ease-in-out;
        }
        .sidebar.collapsed {
            transform: translateX(-100%);
        }
        /* Style untuk menu aktif yang baru */
        .menu-link.active {
            background-color: var(--retro-teal) !important;
            color: white !important;
        }
        @media (min-width: 768px) {
            .sidebar {
                transform: translateX(0);
            }
        }
    </style>
</head>
<body class="bg-amber-50 text-stone-800">
    <div id="sidebar" class="sidebar fixed inset-y-0 left-0 z-50 w-64 bg-amber-900 text-amber-100 transform md:translate-x-0 transition-transform duration-300 ease-in-out border-r-2 border-stone-800">
        <div class="flex items-center justify-between p-4 border-b-2 border-amber-800">
            <h1 class="font-serif text-2xl text-amber-50">SIMPRAK</h1>
            <button id="closeSidebar" class="md:hidden text-amber-200">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="p-4 border-b-2 border-amber-800">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-amber-800 rounded-full flex items-center justify-center border-2 border-amber-700">
                    <i class="fas fa-user-graduate text-amber-200"></i>
                </div>
                <div>
                    <p class="font-semibold text-sm text-white"><?= htmlspecialchars($user_data['nama']) ?></p>
                    <p class="text-xs text-amber-300 font-semibold">Mahasiswa</p>
                </div>
            </div>
             <div class="mt-4 space-y-2">
                <div class="flex justify-between text-xs font-semibold">
                    <span class="text-amber-300">PRAKTIKUM:</span>
                    <span class="bg-yellow-500 text-yellow-900 px-2 py-0.5 rounded-full"><?= $praktikum_count ?></span>
                </div>
                <div class="flex justify-between text-xs font-semibold">
                    <span class="text-amber-300">LAPORAN:</span>
                    <span class="bg-teal-500 text-white px-2 py-0.5 rounded-full"><?= $laporan_count ?></span>
                </div>
            </div>
        </div>
        
        <nav class="p-4 flex-grow">
            <ul class="space-y-2">
                 <li><a href="dashboard.php" class="menu-link flex items-center gap-3 p-3 rounded-none transition-colors text-amber-200 hover:bg-amber-800 <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>"><i class="fas fa-home w-5 text-center"></i><span class="font-semibold">Dashboard</span></a></li>
                <li><a href="praktikum_saya.php" class="menu-link flex items-center gap-3 p-3 rounded-none transition-colors text-amber-200 hover:bg-amber-800 <?= basename($_SERVER['PHP_SELF']) == 'praktikum_saya.php' ? 'active' : '' ?>"><i class="fas fa-book-open w-5 text-center"></i><span class="font-semibold">Praktikum Saya</span></a></li>
                <li><a href="daftar_praktikum.php" class="menu-link flex items-center gap-3 p-3 rounded-none transition-colors text-amber-200 hover:bg-amber-800 <?= basename($_SERVER['PHP_SELF']) == 'daftar_praktikum.php' ? 'active' : '' ?>"><i class="fas fa-plus-circle w-5 text-center"></i><span class="font-semibold">Daftar Praktikum</span></a></li>
                <li><a href="upload_laporan.php" class="menu-link flex items-center gap-3 p-3 rounded-none transition-colors text-amber-200 hover:bg-amber-800 <?= basename($_SERVER['PHP_SELF']) == 'upload_laporan.php' ? 'active' : '' ?>"><i class="fas fa-upload w-5 text-center"></i><span class="font-semibold">Upload Laporan</span></a></li>
                <li><a href="../katalog.php" class="menu-link flex items-center gap-3 p-3 rounded-none transition-colors text-amber-200 hover:bg-amber-800 <?= basename($_SERVER['PHP_SELF']) == 'katalog.php' ? 'active' : '' ?>"><i class="fas fa-search w-5 text-center"></i><span class="font-semibold">Katalog Praktikum</span></a></li>
                <li><a href="keluar_praktikum.php" class="menu-link flex items-center gap-3 p-3 rounded-none transition-colors text-amber-200 hover:bg-amber-800 <?= basename($_SERVER['PHP_SELF']) == 'keluar_praktikum.php' ? 'active' : '' ?>"><i class="fas fa-sign-out-alt w-5 text-center"></i><span class="font-semibold">Keluar Praktikum</span></a></li>
            </ul>
        </nav>
        
        <div class="p-4 border-t-2 border-amber-800">
            <a href="../logout.php" class="flex items-center gap-3 p-3 rounded-none bg-red-800 hover:bg-red-700 transition-colors text-red-100 hover:text-white">
                <i class="fas fa-sign-out-alt w-5 text-center"></i>
                <span class="font-semibold">Logout</span>
            </a>
        </div>
    </div>
    
    <div class="md:ml-64 min-h-screen flex flex-col">
        <header class="bg-amber-100 border-b-2 border-stone-800 sticky top-0 z-40">
            <div class="flex items-center justify-between p-4">
                <div class="flex items-center space-x-4">
                    <button id="openSidebar" class="md:hidden text-stone-700 hover:text-stone-900">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    <h2 class="font-serif text-2xl font-bold text-amber-900"><?= isset($page_title) ? htmlspecialchars($page_title) : 'Dashboard' ?></h2>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-sm text-stone-700 hidden sm:block">
                        <i class="fas fa-clock mr-1"></i>
                        <span id="current-time"></span>
                    </div>
                    <div class="w-8 h-8 bg-amber-800 rounded-full flex items-center justify-center border-2 border-amber-700">
                        <i class="fas fa-user-graduate text-amber-200 text-sm"></i>
                    </div>
                </div>
            </div>
        </header>
        
        <main class="p-6 flex-grow">
            <?php if (isset($_SESSION['success'])): ?>
            <div class="mb-4 p-4 bg-teal-100 border-2 border-teal-800 text-teal-900 rounded-none retro-shadow">
                <i class="fas fa-check-circle mr-3"></i> <span class="font-bold"><?= htmlspecialchars($_SESSION['success']) ?></span>
            </div>
            <?php unset($_SESSION['success']); endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
            <div class="mb-4 p-4 bg-red-100 border-2 border-red-800 text-red-900 rounded-none retro-shadow">
                <i class="fas fa-exclamation-circle mr-3"></i> <span class="font-bold"><?= htmlspecialchars($_SESSION['error']) ?></span>
            </div>
            <?php unset($_SESSION['error']); endif; ?>
            
            <?php if (isset($_SESSION['warning'])): ?>
             <div class="mb-4 p-4 bg-yellow-100 border-2 border-yellow-800 text-yellow-900 rounded-none retro-shadow">
                <i class="fas fa-exclamation-triangle mr-3"></i> <span class="font-bold"><?= htmlspecialchars($_SESSION['warning']) ?></span>
            </div>
            <?php unset($_SESSION['warning']); endif; ?>