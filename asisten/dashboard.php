<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'asisten') {
    header('Location: ../login.php');
    exit();
}

$page_title = 'Dashboard';

// Query untuk mengambil statistik asisten
$stats_sql = "SELECT 
    (SELECT COUNT(*) FROM modul m JOIN mata_praktikum mp ON m.praktikum_id = mp.id WHERE mp.asisten_id = ?) as total_modul,
    (SELECT COUNT(*) FROM laporan l JOIN modul m ON l.modul_id = m.id JOIN mata_praktikum mp ON m.praktikum_id = mp.id WHERE mp.asisten_id = ?) as total_laporan,
    (SELECT COUNT(*) FROM laporan l JOIN modul m ON l.modul_id = m.id JOIN mata_praktikum mp ON m.praktikum_id = mp.id WHERE mp.asisten_id = ? AND l.nilai IS NULL) as menunggu_nilai";
$stmt = $conn->prepare($stats_sql);
$stmt->bind_param("iii", $_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id']);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();

// Include header template
include 'templates/header.php';
?>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    
    <div class="bg-white p-6 rounded-lg shadow-md flex items-center space-x-4">
        <div class="bg-blue-100 p-3 rounded-full">
            <i class="fas fa-book text-blue-600 text-xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">Total Modul Diajarkan</p>
            <p class="text-2xl font-bold text-gray-800"><?php echo $stats['total_modul']; ?></p>
        </div>
    </div>
    
    <div class="bg-white p-6 rounded-lg shadow-md flex items-center space-x-4">
        <div class="bg-green-100 p-3 rounded-full">
            <i class="fas fa-inbox text-green-600 text-xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">Total Laporan Masuk</p>
            <p class="text-2xl font-bold text-gray-800"><?php echo $stats['total_laporan']; ?></p>
        </div>
    </div>

    <div class="bg-white p-6 rounded-lg shadow-md flex items-center space-x-4">
        <div class="bg-yellow-100 p-3 rounded-full">
            <i class="fas fa-clock text-yellow-600 text-xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">Laporan Belum Dinilai</p>
            <p class="text-2xl font-bold text-gray-800"><?php echo $stats['menunggu_nilai']; ?></p>
        </div>
    </div>
</div>

<div class="bg-white p-6 rounded-lg shadow-md mt-8">
    <h3 class="text-xl font-bold text-gray-800 mb-4">Aktivitas Laporan Terbaru</h3>
    <div class="space-y-4">
        <div class="flex items-center">
            <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center mr-4">
                <span class="font-bold text-gray-500">BS</span>
            </div>
            <div>
                <p class="text-gray-800"><strong>Budi Santoso</strong> mengumpulkan laporan untuk <strong>Modul 2</strong></p>
                <p class="text-sm text-gray-500">10 menit lalu</p>
            </div>
        </div>
        <div class="flex items-center">
            <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center mr-4">
                <span class="font-bold text-gray-500">CL</span>
            </div>
            <div>
                <p class="text-gray-800"><strong>Citra Lestari</strong> mengumpulkan laporan untuk <strong>Modul 2</strong></p>
                <p class="text-sm text-gray-500">45 menit lalu</p>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer template
include 'templates/footer.php';
?>