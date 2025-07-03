<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

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
        echo "<script>alert('Berhasil keluar dari praktikum!'); window.location.href='keluar_praktikum.php';</script>";
    } else {
        echo "<script>alert('Gagal keluar dari praktikum. Silakan coba lagi.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keluar Praktikum - SIMPRAK</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4f46e5;
            --danger-color: #ef4444;
            --danger-hover: #dc2626;
            --text-dark: #111827;
            --text-light: #6b7280;
            --background-light: #f9fafb;
            --card-bg: #ffffff;
            --border-color: #e5e7eb;
            --warning-bg: #fefce8;
            --warning-text: #a16207;
            --warning-border: #facc15;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background-color: var(--background-light); color: var(--text-dark); }
        .dashboard-container { display: flex; }

        /* Sidebar - Konsisten dengan Dashboard */
        .sidebar {
            width: 260px;
            background: var(--card-bg);
            border-right: 1px solid var(--border-color);
            padding: 24px 0;
            position: fixed;
            height: 100vh;
            display: flex;
            flex-direction: column;
            transition: transform 0.3s ease-in-out;
        }
        .sidebar-header { text-align: center; padding: 0 24px 24px; margin-bottom: 24px; }
        .sidebar-logo { display: flex; align-items: center; justify-content: center; gap: 12px; font-size: 1.5rem; font-weight: 700; color: var(--primary-color); margin-bottom: 8px; }
        .sidebar-subtitle { color: var(--text-light); font-size: 0.875rem; }
        .sidebar-menu { padding: 0 16px; flex-grow: 1; }
        .menu-item { margin-bottom: 8px; }
        .menu-link { display: flex; align-items: center; padding: 12px 16px; color: var(--text-light); text-decoration: none; border-radius: 8px; transition: all 0.2s ease; font-weight: 500; }
        .menu-link:hover { background-color: var(--background-light); color: var(--primary-color); }
        .menu-link.active { background-color: var(--primary-color); color: white; }
        .menu-icon { width: 20px; margin-right: 16px; font-size: 1.1rem; }
        .logout-item { margin-top: auto; padding: 0 16px; }

        /* Main Content */
        .main-content { flex: 1; margin-left: 260px; padding: 32px; }
        .content-header { margin-bottom: 32px; }
        .page-title { font-size: 2.25rem; font-weight: 700; color: var(--text-dark); margin-bottom: 4px; display: flex; align-items: center; gap: 12px; }
        .page-subtitle { font-size: 1.125rem; color: var(--text-light); }
        
        /* Warning Alert */
        .warning-alert {
            background-color: var(--warning-bg);
            border-left: 4px solid var(--warning-border);
            color: var(--warning-text);
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 32px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }
        .warning-icon { font-size: 1.25rem; margin-top: 2px; }
        .warning-text { font-size: 0.9rem; line-height: 1.6; }

        /* Daftar Praktikum */
        .praktikum-list {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        .praktikum-item {
            background-color: var(--card-bg);
            border-radius: 12px;
            border: 1px solid var(--border-color);
            padding: 16px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: box-shadow 0.2s ease;
        }
        .praktikum-item:hover {
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.05), 0 2px 4px -2px rgb(0 0 0 / 0.05);
        }
        .praktikum-info h3 { font-size: 1.125rem; font-weight: 600; }
        .praktikum-info .praktikum-code { font-size: 0.875rem; color: var(--text-light); }
        
        .btn-danger {
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 600;
            transition: all 0.2s ease;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background-color: #fee2e2;
            color: var(--danger-color);
            border: 1px solid #fecaca;
        }
        .btn-danger:hover {
            background-color: var(--danger-color);
            color: white;
            border-color: var(--danger-color);
        }
        
        /* Empty State */
        .empty-state { text-align: center; padding: 60px 40px; background-color: var(--card-bg); border-radius: 12px; border: 1px solid var(--border-color); }
        .empty-icon { font-size: 4rem; color: #d1d5db; margin-bottom: 24px; }
        .empty-title { font-size: 1.5rem; font-weight: 600; margin-bottom: 8px; }
        .empty-desc { color: var(--text-light); margin-bottom: 24px; max-width: 400px; margin-left: auto; margin-right: auto; }
        .empty-action { display: inline-block; background-color: var(--primary-color); color: white; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: 600; transition: background-color 0.2s ease; }
        .empty-action:hover { background-color: var(--primary-hover); }

        /* Responsive */
        .mobile-menu-toggle { display: none; }
        @media (max-width: 1024px) {
            .sidebar { transform: translateX(-100%); z-index: 1000; box-shadow: 0 0 40px rgba(0,0,0,0.1); }
            .sidebar.open { transform: translateX(0); }
            .main-content { margin-left: 0; }
            .mobile-menu-toggle { display: block; position: fixed; top: 20px; left: 20px; z-index: 1001; background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 8px; width: 44px; height: 44px; font-size: 1.2rem; color: var(--text-light); cursor: pointer; }
        }
        @media (max-width: 768px) {
            .main-content { padding: 24px; }
            .page-title { font-size: 1.75rem; }
            .praktikum-item { flex-direction: column; align-items: flex-start; gap: 16px; }
        }

    </style>
</head>
<body>
    <button class="mobile-menu-toggle" onclick="toggleSidebar()"> <i class="fas fa-bars"></i> </button>

    <div class="dashboard-container">
        <div class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo"><i class="fas fa-graduation-cap"></i><span>SIMPRAK</span></div>
                <div class="sidebar-subtitle">Portal Mahasiswa</div>
            </div>
            <div class="sidebar-menu">
                <div class="menu-item"><a href="dashboard.php" class="menu-link"><i class="fas fa-home menu-icon"></i><span class="menu-text">Dashboard</span></a></div>
                <div class="menu-item"><a href="praktikum_saya.php" class="menu-link"><i class="fas fa-book-open menu-icon"></i><span class="menu-text">Praktikum Saya</span></a></div>
                <div class="menu-item"><a href="daftar_praktikum.php" class="menu-link"><i class="fas fa-list-alt menu-icon"></i><span class="menu-text">Daftar Praktikum</span></a></div>
                <div class="menu-item"><a href="detail_praktikum.php" class="menu-link"><i class="fas fa-info-circle menu-icon"></i><span class="menu-text">Detail Praktikum</span></a></div>
                <div class="menu-item"><a href="upload_laporan.php" class="menu-link"><i class="fas fa-upload menu-icon"></i><span class="menu-text">Upload Laporan</span></a></div>
                <div class="menu-item"><a href="keluar_praktikum.php" class="menu-link active"><i class="fas fa-sign-out-alt menu-icon"></i><span class="menu-text">Keluar Praktikum</span></a></div>
            </div>
            <div class="logout-item">
                <a href="../logout.php" class="menu-link"><i class="fas fa-power-off menu-icon"></i><span class="menu-text">Logout</span></a>
            </div>
        </div>

        <div class="main-content">
            <div class="content-header">
                <h1 class="page-title"><i class="fas fa-sign-out-alt"></i> Keluar Praktikum</h1>
                <p class="page-subtitle">Pilih praktikum yang ingin Anda tinggalkan.</p>
            </div>

            <div class="warning-alert">
                <i class="fas fa-exclamation-triangle warning-icon"></i>
                <div class="warning-text">
                    <strong>Peringatan Penting:</strong> Keluar dari praktikum adalah tindakan permanen dan akan menghapus semua data pendaftaran Anda. Data laporan yang sudah diupload mungkin juga akan terpengaruh.
                </div>
            </div>

            <div class="card-section">
                <h2 class="section-title" style="margin-bottom: 16px;"><i class="fas fa-list"></i> Praktikum yang Anda Ikuti</h2>
                <?php if ($result && $result->num_rows > 0): ?>
                    <div class="praktikum-list">
                        <?php while($praktikum = $result->fetch_assoc()): ?>
                            <div class="praktikum-item">
                                <div class="praktikum-info">
                                    <h3><?php echo htmlspecialchars($praktikum['nama_mk']); ?></h3>
                                    <div class="praktikum-code"><?php echo htmlspecialchars($praktikum['kode_mk']); ?></div>
                                </div>
                                <div class="praktikum-actions">
                                    <form method="post" style="display: contents;">
                                        <input type="hidden" name="praktikum_id" value="<?php echo $praktikum['praktikum_id']; ?>">
                                        <button type="submit" class="btn-danger" onclick="return confirm('Anda yakin ingin keluar dari praktikum \'<?php echo htmlspecialchars($praktikum['nama_mk']); ?>\'? Tindakan ini tidak dapat dibatalkan.')">
                                            <i class="fas fa-times-circle"></i> Keluar
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state" style="padding: 40px; box-shadow: none;">
                        <div class="empty-icon" style="font-size: 2.5rem;"><i class="fas fa-check-circle"></i></div>
                        <div class="empty-title" style="font-size: 1.25rem;">Tidak Ada Praktikum</div>
                        <div class="empty-desc">Anda saat ini tidak terdaftar di praktikum mana pun.</div>
                    </div>
                <?php endif; ?>
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
            if (window.innerWidth <= 1024 && sidebar.classList.contains('open')) {
                if (!sidebar.contains(event.target) && !mobileToggle.contains(event.target)) {
                    sidebar.classList.remove('open');
                }
            }
        });
    </script>
</body>
</html>