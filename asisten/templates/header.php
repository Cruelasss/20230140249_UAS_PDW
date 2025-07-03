<?php
// Logika PHP Anda di atas (TIDAK DIUBAH)
// session_start(); // Diasumsikan sudah ada dari file utama
// require_once '../config.php'; // Diasumsikan sudah ada dari file utama

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'asisten') {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
// Data user dummy untuk contoh, gunakan koneksi asli Anda
if (empty($user_data)) {
    $user_data = ['nama' => 'Nama Asisten', 'email' => 'asisten@example.com'];
}
// $user_query = "SELECT nama, email FROM users WHERE id = ?";
// $stmt = $conn->prepare($user_query);
// $stmt->bind_param("i", $user_id);
// $stmt->execute();
// $user_result = $stmt->get_result();
// $user_data = $user_result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? $page_title . ' - SIMPRAK' : 'SIMPRAK - Portal Asisten' ?></title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=Public+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            /* Definisi variabel warna untuk kemudahan referensi */
            --retro-border: #4d4d4d;
        }
        body {
            /* Menerapkan font dasar */
            font-family: 'Public Sans', sans-serif;
        }
        .font-serif {
            /* Kelas helper untuk font judul */
            font-family: 'DM Serif Display', serif;
        }
        .retro-shadow {
            /* Kelas helper untuk bayangan retro */
            box-shadow: 4px 4px 0px var(--retro-border);
        }
        .sidebar {
            transition: transform 0.3s ease-in-out;
        }
        .sidebar.collapsed {
            transform: translateX(-100%);
        }
        @media (min-width: 768px) {
            .sidebar {
                transform: translateX(0);
            }
        }
    </style>
</head>
<body class="bg-amber-50">
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
                    <i class="fas fa-user text-amber-200"></i>
                </div>
                <div>
                    <p class="font-semibold text-white"><?= htmlspecialchars($user_data['nama']) ?></p>
                    <p class="text-sm text-amber-200"><?= htmlspecialchars($user_data['email']) ?></p>
                    <p class="text-xs text-amber-300 font-semibold">Asisten</p>
                </div>
            </div>
        </div>
        
        <nav class="p-4">
            <ul class="space-y-2">
                <li>
                    <a href="dashboard.php" class="flex items-center space-x-3 p-3 rounded-none hover:bg-amber-800 transition-colors <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'bg-amber-800' : '' ?>">
                        <i class="fas fa-tachometer-alt w-5"></i>
                        <span class="font-semibold">Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="kelola_praktikum.php" class="flex items-center space-x-3 p-3 rounded-none hover:bg-amber-800 transition-colors <?= basename($_SERVER['PHP_SELF']) == 'kelola_praktikum.php' ? 'bg-amber-800' : '' ?>">
                        <i class="fas fa-book w-5"></i>
                        <span class="font-semibold">Kelola Praktikum</span>
                    </a>
                </li>
                <li>
                    <a href="kelola_modul.php" class="flex items-center space-x-3 p-3 rounded-none hover:bg-amber-800 transition-colors <?= basename($_SERVER['PHP_SELF']) == 'kelola_modul.php' ? 'bg-amber-800' : '' ?>">
                        <i class="fas fa-file-alt w-5"></i>
                        <span class="font-semibold">Kelola Modul</span>
                    </a>
                </li>
                <li>
                    <a href="laporan_masuk.php" class="flex items-center space-x-3 p-3 rounded-none hover:bg-amber-800 transition-colors <?= basename($_SERVER['PHP_SELF']) == 'laporan_masuk.php' ? 'bg-amber-800' : '' ?>">
                        <i class="fas fa-inbox w-5"></i>
                        <span class="font-semibold">Laporan Masuk</span>
                    </a>
                </li>
                <li>
                    <a href="kelola_pengguna.php" class="flex items-center space-x-3 p-3 rounded-none hover:bg-amber-800 transition-colors <?= basename($_SERVER['PHP_SELF']) == 'kelola_pengguna.php' ? 'bg-amber-800' : '' ?>">
                        <i class="fas fa-users w-5"></i>
                        <span class="font-semibold">Kelola Pengguna</span>
                    </a>
                </li>
            </ul>
        </nav>
        
        <div class="absolute bottom-0 left-0 right-0 p-4 border-t-2 border-amber-800">
            <a href="../logout.php" class="flex items-center space-x-3 p-3 rounded-none bg-red-800 hover:bg-red-700 transition-colors text-red-100 hover:text-white">
                <i class="fas fa-sign-out-alt w-5"></i>
                <span class="font-semibold">Logout</span>
            </a>
        </div>
    </div>
    
    <div class="md:ml-64 min-h-screen">
        <header class="bg-amber-100 border-b-2 border-stone-800">
            <div class="flex items-center justify-between p-4">
                <div class="flex items-center space-x-4">
                    <button id="openSidebar" class="md:hidden text-stone-700 hover:text-stone-900">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    <h2 class="font-serif text-2xl font-bold text-amber-900"><?= isset($page_title) ? $page_title : 'Dashboard' ?></h2>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-sm text-stone-700 hidden sm:block">
                        <i class="fas fa-clock mr-1"></i>
                        <span id="current-time"></span>
                    </div>
                    <div class="w-8 h-8 bg-amber-800 rounded-full flex items-center justify-center border-2 border-amber-700">
                        <i class="fas fa-user text-amber-200 text-sm"></i>
                    </div>
                </div>
            </div>
        </header>
        
        <main class="p-6">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="mb-4 p-4 bg-teal-100 border-2 border-teal-800 text-teal-900 rounded-none retro-shadow">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle mr-3"></i>
                        <span class="font-bold"><?= htmlspecialchars($_SESSION['success']) ?></span>
                    </div>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="mb-4 p-4 bg-red-100 border-2 border-red-800 text-red-900 rounded-none retro-shadow">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle mr-3"></i>
                        <span class="font-bold"><?= htmlspecialchars($_SESSION['error']) ?></span>
                    </div>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['warning'])): ?>
                 <div class="mb-4 p-4 bg-yellow-100 border-2 border-yellow-800 text-yellow-900 rounded-none retro-shadow">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-triangle mr-3"></i>
                        <span class="font-bold"><?= htmlspecialchars($_SESSION['warning']) ?></span>
                    </div>
                </div>
                <?php unset($_SESSION['warning']); ?>
            <?php endif; ?>

<script>
    const sidebar = document.getElementById('sidebar');
    const openSidebarBtn = document.getElementById('openSidebar');
    const closeSidebarBtn = document.getElementById('closeSidebar');
    
    openSidebarBtn.addEventListener('click', () => {
        sidebar.classList.remove('collapsed');
    });
    
    closeSidebarBtn.addEventListener('click', () => {
        sidebar.classList.add('collapsed');
    });

    function updateTime() {
        const now = new Date();
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit', timeZone: 'Asia/Jakarta' };
        document.getElementById('current-time').textContent = now.toLocaleDateString('id-ID', options);
    }
    
    updateTime();
    setInterval(updateTime, 1000);
</script>