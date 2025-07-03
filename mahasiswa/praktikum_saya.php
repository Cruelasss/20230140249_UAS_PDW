<?php
// --- FUNGSI PHP ANDA, TIDAK DIUBAH SAMA SEKALI ---
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Ambil semua praktikum yang diikuti mahasiswa
$sql_praktikum = "SELECT pp.praktikum_id, mp.nama_mk, mp.kode_mk
    FROM pendaftaran_praktikum pp
    JOIN mata_praktikum mp ON pp.praktikum_id = mp.id
    WHERE pp.mahasiswa_id = ?
    ORDER BY mp.nama_mk ASC";
$stmt = $conn->prepare($sql_praktikum);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$praktikum_result = $stmt->get_result();
// --- AKHIR DARI FUNGSI PHP ANDA ---
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Praktikum Saya - SIMPRAK</title>
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
            --retro-red: #ef4444;
            --sidebar-bg: #362314;
            --sidebar-text: #eaddc7;
            --sidebar-active: #c69c6d;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Public Sans', sans-serif; background-color: var(--retro-bg); color: var(--retro-text); }
        .font-serif { font-family: 'DM Serif Display', serif; }
        .retro-shadow { box-shadow: 6px 6px 0px var(--retro-border); }
        .dashboard-container { display: flex; }

        /* Sidebar */
        .sidebar {
            width: 260px; background: var(--sidebar-bg); border-right: 2px solid var(--retro-border); padding: 24px 0;
            position: fixed; height: 100vh; display: flex; flex-direction: column; transition: transform 0.3s ease-in-out;
        }
        .sidebar-header { text-align: center; padding: 0 24px 24px; margin-bottom: 16px; border-bottom: 2px solid #573d24; }
        .sidebar-logo { display: flex; align-items: center; justify-content: center; gap: 12px; font-family: 'DM Serif Display', serif; font-size: 1.75rem; font-weight: 700; color: var(--sidebar-text); }
        .sidebar-subtitle { color: var(--sidebar-active); font-size: 0.875rem; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; }
        .sidebar-menu { padding: 0 16px; flex-grow: 1; }
        .menu-item { margin-bottom: 8px; }
        .menu-link { display: flex; align-items: center; padding: 12px 16px; color: var(--sidebar-text); text-decoration: none; transition: all 0.2s ease; font-weight: 700; }
        .menu-link:hover { background-color: #573d24; color: white; }
        .menu-link.active { background-color: var(--sidebar-active); color: var(--sidebar-bg); }
        .menu-icon { width: 20px; margin-right: 16px; font-size: 1.1rem; text-align: center; }
        .logout-item { margin-top: auto; padding: 0 16px; }

        /* Main Content */
        .main-content { flex: 1; margin-left: 260px; padding: 32px; }
        .content-header { margin-bottom: 32px; }
        .page-title { font-family: 'DM Serif Display', serif; font-size: 2.5rem; font-weight: 700; color: var(--retro-heading); display: flex; align-items: center; gap: 12px; }
        .page-subtitle { font-size: 1.125rem; color: var(--retro-text); }

        /* Kartu Praktikum */
        .praktikum-grid { display: grid; gap: 32px; }
        .praktikum-card { background-color: var(--retro-card-bg); border: 2px solid var(--retro-border); overflow: hidden; }
        .praktikum-header { display: flex; align-items: center; gap: 16px; padding: 24px; border-bottom: 2px solid var(--retro-border); }
        .praktikum-icon { flex-shrink: 0; width: 52px; height: 52px; background-color: var(--retro-yellow); color: var(--retro-heading); display: flex; align-items: center; justify-content: center; border: 2px solid var(--retro-border); font-size: 1.75rem; }
        .praktikum-info h3 { font-family: 'DM Serif Display', serif; font-size: 1.75rem; color: var(--retro-heading); }
        .praktikum-info .praktikum-code { font-size: 1rem; color: var(--retro-text); font-weight: 500; }

        /* Daftar Modul */
        .modul-list { background-color: var(--retro-bg); }
        .modul-item { display: flex; justify-content: space-between; align-items: center; padding: 16px 24px; border-bottom: 2px dashed #dcd1b3; }
        .modul-list .modul-item:last-child { border-bottom: none; }
        .modul-title { font-weight: 700; color: var(--retro-text); }
        .modul-details { display: flex; align-items: center; gap: 16px; }
        
        .status-badge { padding: 4px 12px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; border: 2px solid var(--retro-border); }
        .status-belum { background-color: #fecaca; color: #991b1b; }
        .status-menunggu { background-color: #fde68a; color: #a16207; }
        .status-nilai { background-color: #99f6e4; color: #0f766e; }
        
        .action-btn { padding: 8px 16px; text-decoration: none; font-size: 0.875rem; font-weight: 700; transition: all 0.2s ease; border: 2px solid var(--retro-border); cursor: pointer; display: inline-flex; align-items: center; gap: 6px; }
        .btn-upload { background-color: var(--retro-teal); color: white; }
        .btn-upload:hover { background-color: #0f766e; }
        .btn-update { background-color: var(--retro-orange); color: white; }
        .btn-update:hover { background-color: #c2410c; }
        .btn-lihat { background-color: var(--retro-yellow); color: var(--retro-heading); }
        .btn-lihat:hover { background-color: #ca8a04; }

        /* Empty State */
        .empty-state { text-align: center; padding: 60px 40px; background-color: var(--retro-card-bg); border: 2px solid var(--retro-border); }
        .empty-icon { font-size: 4rem; color: #d6d3d1; margin-bottom: 24px; }
        .empty-title { font-family: 'DM Serif Display', serif; font-size: 1.5rem; font-weight: 600; margin-bottom: 8px; }
        .empty-desc { color: var(--retro-text); margin-bottom: 24px; max-width: 400px; margin-left: auto; margin-right: auto; }
        .empty-action { display: inline-block; background-color: var(--retro-teal); color: white; padding: 12px 24px; text-decoration: none; font-weight: 700; border: 2px solid var(--retro-border); transition: all 0.2s ease; }
        .empty-action:hover { background-color: #0f766e; }

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
                <div class="menu-item"><a href="dashboard.php" class="menu-link"><i class="fas fa-home menu-icon"></i><span class="menu-text">Dashboard</span></a></div>
                <div class="menu-item"><a href="praktikum_saya.php" class="menu-link active"><i class="fas fa-book-open menu-icon"></i><span class="menu-text">Praktikum Saya</span></a></div>
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
                <h1 class="page-title"><i class="fas fa-book-open"></i> Praktikum Saya</h1>
                <p class="page-subtitle">Kelola semua modul dan laporan dari praktikum yang Anda ikuti.</p>
            </div>

            <?php if ($praktikum_result && $praktikum_result->num_rows > 0): ?>
                <div class="praktikum-grid">
                    <?php while($praktikum = $praktikum_result->fetch_assoc()): ?>
                        <div class="praktikum-card retro-shadow">
                            <div class="praktikum-header">
                                <div class="praktikum-icon"><i class="fas fa-flask"></i></div>
                                <div class="praktikum-info">
                                    <h3><?php echo htmlspecialchars($praktikum['nama_mk']); ?></h3>
                                    <div class="praktikum-code"><?php echo htmlspecialchars($praktikum['kode_mk']); ?></div>
                                </div>
                            </div>
                            
                            <div class="modul-list">
                            <?php
                            $sql_modul = "SELECT m.id, m.judul_modul FROM modul m WHERE m.praktikum_id = ? ORDER BY m.id ASC";
                            $stmt_modul = $conn->prepare($sql_modul);
                            $stmt_modul->bind_param("i", $praktikum['praktikum_id']);
                            $stmt_modul->execute();
                            $modul_result = $stmt_modul->get_result();
                            if ($modul_result->num_rows > 0):
                                while($modul = $modul_result->fetch_assoc()):
                            ?>
                                    <div class="modul-item">
                                        <div class="modul-title"><?php echo htmlspecialchars($modul['judul_modul']); ?></div>
                                        <div class="modul-details">
                                        <?php
                                        $sql_laporan = "SELECT nilai FROM laporan WHERE mahasiswa_id = ? AND modul_id = ?";
                                        $stmt_laporan = $conn->prepare($sql_laporan);
                                        $stmt_laporan->bind_param("ii", $user_id, $modul['id']);
                                        $stmt_laporan->execute();
                                        $laporan_result = $stmt_laporan->get_result();
                                        $laporan = $laporan_result->fetch_assoc();

                                        if ($laporan) {
                                            if ($laporan['nilai'] !== null) {
                                                echo '<span class="status-badge status-nilai">Nilai: ' . htmlspecialchars($laporan['nilai']) . '</span>';
                                                echo '<a href="upload_laporan.php?modul_id='.$modul['id'].'" class="action-btn btn-lihat"><i class="fas fa-search"></i> Lihat</a>';
                                            } else {
                                                echo '<span class="status-badge status-menunggu">Menunggu Nilai</span>';
                                                echo '<a href="upload_laporan.php?modul_id='.$modul['id'].'" class="action-btn btn-update"><i class="fas fa-edit"></i> Update</a>';
                                            }
                                        } else {
                                            echo '<span class="status-badge status-belum">Belum Upload</span>';
                                            echo '<a href="upload_laporan.php?modul_id='.$modul['id'].'" class="action-btn btn-upload"><i class="fas fa-upload"></i> Upload</a>';
                                        }
                                        ?>
                                        </div>
                                    </div>
                            <?php 
                                endwhile;
                            else:
                            ?>
                                <div style="text-align: center; padding: 24px; color: var(--retro-text);">Belum ada modul untuk praktikum ini.</div>
                            <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="empty-state retro-shadow">
                    <div class="empty-icon"><i class="fas fa-folder-open"></i></div>
                    <h3 class="empty-title">Anda Belum Mengikuti Praktikum</h3>
                    <p class="empty-desc">Silakan mendaftar ke praktikum yang tersedia untuk memulai.</p>
                    <a href="daftar_praktikum.php" class="empty-action">Daftar Praktikum Sekarang</a>
                </div>
            <?php endif; ?>
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