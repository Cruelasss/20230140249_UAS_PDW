<?php
// --- FUNGSI PHP ANDA, TIDAK DIUBAH SAMA SEKALI ---
session_start();
require_once '../config.php';

// Cek apakah user sudah login dan role-nya mahasiswa
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Ambil data mahasiswa
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Statistik praktikum
$stats_sql = "SELECT 
    (SELECT COUNT(*) FROM pendaftaran_praktikum WHERE mahasiswa_id = ?) as total_praktikum,
    (SELECT COUNT(*) FROM laporan l JOIN modul m ON l.modul_id = m.id JOIN pendaftaran_praktikum pp ON m.praktikum_id = pp.praktikum_id WHERE pp.mahasiswa_id = ?) as total_laporan,
    (SELECT COUNT(*) FROM laporan l JOIN modul m ON l.modul_id = m.id JOIN pendaftaran_praktikum pp ON m.praktikum_id = pp.praktikum_id WHERE pp.mahasiswa_id = ? AND l.nilai IS NULL) as menunggu_nilai";
$stats_stmt = $conn->prepare($stats_sql);
$stats_stmt->bind_param("iii", $user_id, $user_id, $user_id);
$stats_stmt->execute();
$stats_result = $stats_stmt->get_result();
$stats = $stats_result->fetch_assoc();
// --- AKHIR DARI FUNGSI PHP ANDA ---
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Mahasiswa - SIMPRAK</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=Public+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        /* ============================================== */
        /* == GAYA CSS DIGANTI TOTAL MENJADI GAYA RETRO == */
        /* ============================================== */
        :root {
            --retro-bg: #fffbeb; 
            --retro-card-bg: #ffffff;
            --retro-text: #3f3c2b; 
            --retro-heading: #78350f; 
            --retro-border: #4d4d4d;
            --retro-yellow: #f59e0b;
            --retro-orange: #f97316;
            --retro-teal: #14b8a6;
            --retro-sky: #38bdf8;
            --sidebar-bg: #362314; /* Dark Brown */
            --sidebar-text: #eaddc7;
            --sidebar-active: #c69c6d;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Public Sans', sans-serif;
            background-color: var(--retro-bg);
            color: var(--retro-text);
        }

        .font-serif {
            font-family: 'DM Serif Display', serif;
        }

        .retro-shadow {
            box-shadow: 6px 6px 0px var(--retro-border);
        }

        .dashboard-container {
            display: flex;
        }

        /* Sidebar */
        .sidebar {
            width: 260px;
            background: var(--sidebar-bg);
            border-right: 2px solid var(--retro-border);
            padding: 24px 0;
            position: fixed;
            height: 100vh;
            display: flex;
            flex-direction: column;
            transition: transform 0.3s ease-in-out;
        }

        .sidebar-header {
            text-align: center;
            padding: 0 24px 24px;
            margin-bottom: 16px;
            border-bottom: 2px solid #573d24;
        }

        .sidebar-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            font-family: 'DM Serif Display', serif;
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--sidebar-text);
        }
        
        .sidebar-subtitle {
            color: var(--sidebar-active);
            font-size: 0.875rem;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .sidebar-menu {
            padding: 0 16px;
            flex-grow: 1;
        }

        .menu-item {
            margin-bottom: 8px;
        }

        .menu-link {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            color: var(--sidebar-text);
            text-decoration: none;
            transition: all 0.2s ease;
            font-weight: 700;
        }

        .menu-link:hover {
            background-color: #573d24;
            color: white;
        }

        .menu-link.active {
            background-color: var(--sidebar-active);
            color: var(--sidebar-bg);
        }

        .menu-icon {
            width: 20px;
            margin-right: 16px;
            font-size: 1.1rem;
            text-align: center;
        }
        
        .logout-item {
            margin-top: auto;
            padding: 0 16px;
        }
        
        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 260px;
            padding: 32px;
        }
        
        .content-header {
            margin-bottom: 32px;
        }

        .welcome-title {
            font-family: 'DM Serif Display', serif;
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--retro-heading);
        }

        .welcome-text {
            font-size: 1.125rem;
            color: var(--retro-text);
        }
        
        .user-info-wrapper {
             background-color: var(--retro-card-bg);
             padding: 24px;
             border: 2px solid var(--retro-border);
             margin-top: 24px;
        }

        .user-info {
            display: flex;
            justify-content: space-around;
            gap: 16px;
            flex-wrap: wrap;
        }
        
        .info-label {
            font-size: 0.875rem;
            color: var(--retro-text);
            font-weight: bold;
            text-transform: uppercase;
        }

        .info-value {
            font-size: 1rem;
            font-weight: 500;
        }
        
        /* Universal Card/Section Styling */
        .card-section {
            background-color: var(--retro-card-bg);
            padding: 24px;
            border: 2px solid var(--retro-border);
            margin-bottom: 32px;
        }
        
        .section-title {
            font-family: 'DM Serif Display', serif;
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--retro-heading);
            margin-bottom: 24px;
            border-bottom: 2px solid var(--retro-border);
            padding-bottom: 1rem;
        }

        /* Stats Section */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 24px;
        }

        .stat-card {
            background-color: var(--retro-card-bg);
            padding: 24px;
            border: 2px solid var(--retro-border);
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .stat-icon {
            width: 52px;
            height: 52px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            border: 2px solid var(--retro-border);
        }

        .stat-icon.praktikum { background-color: var(--retro-yellow); }
        .stat-icon.laporan { background-color: var(--retro-teal); color: white; }
        .stat-icon.menunggu { background-color: var(--retro-orange); color: white; }

        .stat-info .stat-number {
            font-size: 2.25rem;
            font-weight: 700;
            line-height: 1;
            color: var(--retro-heading);
        }

        .stat-info .stat-label {
            font-size: 0.875rem;
            font-weight: bold;
            color: var(--retro-text);
        }

        /* Quick Actions */
        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
        }

        .action-card {
            padding: 20px;
            text-decoration: none;
            border: 2px solid var(--retro-border);
            transition: all 0.2s ease;
            text-align: center;
            background-color: var(--retro-bg);
        }
        
        .action-card:hover {
             transform: translateY(-4px);
             box-shadow: 4px 4px 0px var(--retro-border);
             background-color: #fde68a; /* Light Yellow */
        }

        .action-icon {
            font-size: 2rem;
            margin-bottom: 12px;
            color: var(--retro-heading);
        }
        
        .action-title {
            font-size: 1rem;
            font-weight: 700;
            color: var(--retro-text);
        }

        .action-desc { display: none; } /* Deskripsi tidak digunakan di tema ini agar lebih simpel */

        /* Responsive */
        .mobile-menu-toggle { display: none; }
        @media (max-width: 768px) {
            .main-content { margin-left: 0; }
            .sidebar { transform: translateX(-100%); z-index: 1000; }
            .sidebar.open { transform: translateX(0); }
            .mobile-menu-toggle {
                display: block; position: fixed; top: 20px; left: 20px; z-index: 1001;
                background: var(--retro-card-bg); border: 2px solid var(--retro-border);
                width: 44px; height: 44px; font-size: 1.2rem; cursor: pointer;
            }
        }
    </style>
</head>
<body>
    <button class="mobile-menu-toggle" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>

    <div class="dashboard-container">
        <div class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">
                    <i class="fas fa-graduation-cap"></i>
                    <span>SIMPRAK</span>
                </div>
                <div class="sidebar-subtitle">Portal Mahasiswa</div>
            </div>
            
            <div class="sidebar-menu">
                <div class="menu-item"><a href="dashboard.php" class="menu-link active"><i class="fas fa-home menu-icon"></i><span class="menu-text">Dashboard</span></a></div>
                <div class="menu-item"><a href="praktikum_saya.php" class="menu-link"><i class="fas fa-book-open menu-icon"></i><span class="menu-text">Praktikum Saya</span></a></div>
                <div class="menu-item"><a href="daftar_praktikum.php" class="menu-link"><i class="fas fa-list-alt menu-icon"></i><span class="menu-text">Daftar Praktikum</span></a></div>
                <div class="menu-item"><a href="detail_praktikum.php" class="menu-link"><i class="fas fa-info-circle menu-icon"></i><span class="menu-text">Detail Praktikum</span></a></div>
                <div class="menu-item"><a href="upload_laporan.php" class="menu-link"><i class="fas fa-upload menu-icon"></i><span class="menu-text">Upload Laporan</span></a></div>
                <div class="menu-item"><a href="keluar_praktikum.php" class="menu-link"><i class="fas fa-sign-out-alt menu-icon"></i><span class="menu-text">Keluar Praktikum</span></a></div>
            </div>

            <div class="logout-item">
                <a href="../logout.php" class="menu-link"><i class="fas fa-power-off menu-icon"></i><span class="menu-text">Logout</span></a>
            </div>
        </div>

        <div class="main-content">
            <div class="content-header">
                <h1 class="welcome-title">Selamat Datang, <?php echo htmlspecialchars(explode(' ', $user['nama'])[0]); ?>!</h1>
                <p class="welcome-text">Kelola semua aktivitas praktikum Anda di sini.</p>
                
                <div class="user-info-wrapper retro-shadow">
                    <div class="user-info">
                        <div class="info-item">
                            <div class="info-label">Nama Lengkap</div>
                            <div class="info-value"><?php echo htmlspecialchars($user['nama']); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">NIM</div>
                            <div class="info-value"><?php echo htmlspecialchars($user['nim'] ?? 'N/A'); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Status</div>
                            <div class="info-value">Mahasiswa Aktif</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-section">
                <h2 class="section-title">Statistik Aktivitas</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon praktikum"><i class="fas fa-graduation-cap"></i></div>
                        <div class="stat-info">
                            <div class="stat-number"><?php echo $stats['total_praktikum'] ?? 0; ?></div>
                            <div class="stat-label">Praktikum Diikuti</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon laporan"><i class="fas fa-file-alt"></i></div>
                        <div class="stat-info">
                            <div class="stat-number"><?php echo $stats['total_laporan'] ?? 0; ?></div>
                            <div class="stat-label">Laporan Terkumpul</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon menunggu"><i class="fas fa-clock"></i></div>
                        <div class="stat-info">
                            <div class="stat-number"><?php echo $stats['menunggu_nilai'] ?? 0; ?></div>
                            <div class="stat-label">Menunggu Penilaian</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-section">
                <h2 class="section-title">Akses Cepat</h2>
                <div class="actions-grid">
                    <a href="praktikum_saya.php" class="action-card">
                        <i class="fas fa-book-open action-icon"></i>
                        <div class="action-title">Praktikum Saya</div>
                    </a>
                    <a href="daftar_praktikum.php" class="action-card">
                        <i class="fas fa-list-alt action-icon"></i>
                        <div class="action-title">Daftar Praktikum</div>
                    </a>
                    <a href="upload_laporan.php" class="action-card">
                        <i class="fas fa-upload action-icon"></i>
                        <div class="action-title">Upload Laporan</div>
                    </a>
                    <a href="detail_praktikum.php" class="action-card">
                        <i class="fas fa-info-circle action-icon"></i>
                        <div class="action-title">Lihat Nilai</div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('open');
        }
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const mobileToggle = document.querySelector('.mobile-menu-toggle');
            if (window.innerWidth <= 768 && sidebar.classList.contains('open')) {
                if (!sidebar.contains(event.target) && !mobileToggle.contains(event.target)) {
                    sidebar.classList.remove('open');
                }
            }
        });
    </script>
</body>
</html>