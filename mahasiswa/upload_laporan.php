<?php
// --- FUNGSI PHP ANDA, TIDAK DIUBAH SAMA SEKALI ---
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$page_title = 'Upload Laporan';

// Ambil semua modul dari praktikum yang diikuti mahasiswa
$sql = "SELECT m.id, m.judul_modul, m.deskripsi_modul, mp.nama_mk, mp.kode_mk,
        (SELECT COUNT(*) FROM laporan l WHERE l.modul_id = m.id AND l.mahasiswa_id = ?) as sudah_upload,
        (SELECT l.nilai FROM laporan l WHERE l.modul_id = m.id AND l.mahasiswa_id = ? LIMIT 1) as nilai
        FROM modul m
        JOIN mata_praktikum mp ON m.praktikum_id = mp.id
        JOIN pendaftaran_praktikum pp ON mp.id = pp.praktikum_id
        WHERE pp.mahasiswa_id = ?
        ORDER BY mp.nama_mk ASC, m.id ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $user_id, $user_id, $user_id);
$stmt->execute();
$modul_list_result = $stmt->get_result();
$modul_options = $modul_list_result->fetch_all(MYSQLI_ASSOC);
$stmt->close();


// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['modul_id']) && isset($_FILES['laporan'])) {
    $modul_id = $_POST['modul_id'];
    $file = $_FILES['laporan'];
    
    // Validasi file
    $allowed_types = ['pdf', 'doc', 'docx'];
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($file_extension, $allowed_types)) {
        echo "<script>alert('Format file tidak didukung. Gunakan PDF, DOC, atau DOCX.');</script>";
    } elseif ($file['size'] > 10 * 1024 * 1024) { // 10MB limit
        echo "<script>alert('Ukuran file terlalu besar. Maksimal 10MB.');</script>";
    } else {
        // Generate unique filename
        $filename = time() . '_' . $user_id . '_' . $modul_id . '.' . $file_extension;
        $upload_path = '../uploads/laporan/' . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $upload_path)) {
            // Check if already uploaded
            $check_sql = "SELECT id FROM laporan WHERE mahasiswa_id = ? AND modul_id = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("ii", $user_id, $modul_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows > 0) {
                // Update existing laporan
                $update_sql = "UPDATE laporan SET file_laporan = ?, tanggal_upload = NOW(), nilai = NULL WHERE mahasiswa_id = ? AND modul_id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("sii", $filename, $user_id, $modul_id);
                
                if ($update_stmt->execute()) {
                    echo "<script>alert('Laporan berhasil diperbarui!'); window.location.href='upload_laporan.php';</script>";
                } else {
                    echo "<script>alert('Gagal memperbarui laporan. Silakan coba lagi.');</script>";
                }
            } else {
                // Insert new laporan
                $insert_sql = "INSERT INTO laporan (mahasiswa_id, modul_id, file_laporan, tanggal_upload) VALUES (?, ?, ?, NOW())";
                $insert_stmt = $conn->prepare($insert_sql);
                $insert_stmt->bind_param("iis", $user_id, $modul_id, $filename);
                
                if ($insert_stmt->execute()) {
                    echo "<script>alert('Laporan berhasil diupload!'); window.location.href='upload_laporan.php';</script>";
                } else {
                    echo "<script>alert('Gagal upload laporan. Silakan coba lagi.');</script>";
                }
            }
        } else {
            echo "<script>alert('Gagal upload file. Silakan coba lagi.');</script>";
        }
    }
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
                    <li><a href="upload_laporan.php" class="menu-link active flex items-center gap-3 p-3 rounded-none transition-colors"><i class="fas fa-upload w-5 text-center"></i><span class="font-semibold">Upload Laporan</span></a></li>
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
                <p class="text-stone-700 mt-1 text-lg">Pilih modul dan upload file laporan praktikum Anda.</p>
            </header>

            <?php if (count($modul_options) > 0): ?>
            <div class="space-y-8">
                <div class="bg-white border-2 border-stone-800 p-6 retro-shadow">
                    <h2 class="font-serif text-2xl font-bold text-amber-900 mb-4">Langkah 1: Pilih Modul</h2>
                    <label for="modul-select" class="block text-sm font-bold text-stone-800 mb-2">Pilih modul yang akan diupload laporannya:</label>
                    <select id="modul-select" class="w-full p-3 bg-amber-50 border-2 border-stone-800 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500" onchange="showModulInfo()">
                        <option value="">-- Silakan Pilih Modul --</option>
                        <?php 
                        $current_praktikum = '';
                        foreach ($modul_options as $row) {
                            if ($current_praktikum != $row['nama_mk']) {
                                if ($current_praktikum != '') echo '</optgroup>';
                                echo '<optgroup label="' . htmlspecialchars($row['nama_mk']) . '">';
                                $current_praktikum = $row['nama_mk'];
                            }
                            $status_text = '';
                            if ($row['sudah_upload'] > 0) {
                                $status_text = ' - ' . ($row['nilai'] !== null ? 'Nilai: ' . $row['nilai'] : 'Menunggu Penilaian');
                            }
                            echo '<option value="' . $row['id'] . '" ' . 
                                 'data-title="' . htmlspecialchars($row['judul_modul']) . '" ' .
                                 'data-praktikum="' . htmlspecialchars($row['nama_mk']) . '" ' .
                                 'data-description="' . htmlspecialchars($row['deskripsi_modul'] ?: 'Deskripsi belum tersedia') . '" ' .
                                 'data-uploaded="' . $row['sudah_upload'] . '" ' .
                                 'data-nilai="' . $row['nilai'] . '">' .
                                 htmlspecialchars($row['judul_modul']) . $status_text .
                                 '</option>';
                        }
                        if ($current_praktikum != '') echo '</optgroup>';
                        ?>
                    </select>
                </div>
                
                <div id="upload-section" class="bg-white border-2 border-stone-800 p-6 retro-shadow" style="display: none;">
                    <h2 class="font-serif text-2xl font-bold text-amber-900 mb-4">Langkah 2: Detail & Upload</h2>
                    <div class="flex justify-between items-start gap-4 p-4 border-2 border-dashed border-stone-300 mb-6">
                        <div>
                            <div id="modul-title" class="font-bold text-lg text-stone-800"></div>
                            <div id="modul-praktikum-title" class="text-sm text-stone-600"></div>
                        </div>
                        <div id="modul-status" class="flex-shrink-0"></div>
                    </div>
                    
                    <form method="post" enctype="multipart/form-data" id="laporan-form">
                        <input type="hidden" name="modul_id" id="selected-modul-id">
                        <div class="upload-dropzone text-center p-8 border-2 border-dashed border-stone-800 bg-amber-50" id="dropzone">
                            <input type="file" name="laporan" id="file-input" class="hidden" accept=".pdf,.doc,.docx" onchange="updateFileName(this)">
                            <i class="fas fa-cloud-upload-alt text-5xl text-stone-400 mb-4"></i>
                            <p class="font-bold text-stone-800">Seret & lepas file, atau klik tombol di bawah</p>
                            <p class="text-sm text-stone-600 mb-4">Format: PDF, DOC, DOCX (Maks. 10MB)</p>
                            <label for="file-input" class="inline-block bg-stone-800 text-white font-bold p-3 border-2 border-stone-800 hover:bg-stone-700 cursor-pointer">Pilih File</label>
                            <p id="selected-file-info" class="text-sm text-stone-600 mt-4 font-semibold" style="display: none;"></p>
                        </div>
                        <div class="text-right mt-4">
                            <button type="submit" class="font-bold bg-teal-500 text-white p-3 border-2 border-stone-800 hover:bg-teal-600 disabled:bg-stone-400 disabled:cursor-not-allowed" id="submit-btn" disabled>
                                <i class="fas fa-check-circle"></i> UPLOAD SEKARANG
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <?php else: ?>
                <div class="bg-white border-2 border-stone-800 p-8 text-center retro-shadow">
                    <i class="fas fa-folder-open text-6xl text-stone-400 mb-4"></i>
                    <h3 class="font-serif text-2xl font-bold text-amber-900">Anda Belum Mengikuti Praktikum</h3>
                    <p class="text-stone-600">Daftar untuk praktikum terlebih dahulu untuk bisa mengupload laporan.</p>
                    <div class="mt-6">
                        <a href="daftar_praktikum.php" class="inline-block bg-teal-500 text-white font-bold p-3 border-2 border-stone-800 hover:bg-teal-600">
                            Daftar Praktikum Sekarang
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // --- JAVASCRIPT ANDA, TIDAK DIUBAH SAMA SEKALI ---
        function showModulInfo() {
            const select = document.getElementById('modul-select');
            const uploadSection = document.getElementById('upload-section');
            const selectedOption = select.options[select.selectedIndex];
            
            if (!select.value) {
                uploadSection.style.display = 'none';
                return;
            }

            const title = selectedOption.dataset.title;
            const praktikum = selectedOption.dataset.praktikum;
            const uploaded = selectedOption.dataset.uploaded;
            const nilai = selectedOption.dataset.nilai;

            document.getElementById('modul-title').textContent = title;
            document.getElementById('modul-praktikum-title').textContent = praktikum;
            document.getElementById('selected-modul-id').value = select.value;
            
            let statusHtml = '';
            let statusClass = 'font-bold text-xs uppercase px-3 py-1 border-2 border-stone-800 ';
            if (uploaded === '1') {
                if (nilai) {
                    statusClass += 'bg-green-300 text-green-900';
                    statusHtml = `Nilai: ${nilai}`;
                } else {
                    statusClass += 'bg-yellow-300 text-yellow-900';
                    statusHtml = 'Menunggu Penilaian';
                }
            } else {
                statusClass += 'bg-red-300 text-red-900';
                statusHtml = 'Belum Upload';
            }
            document.getElementById('modul-status').innerHTML = `<span class="${statusClass}">${statusHtml}</span>`;
            
            document.getElementById('laporan-form').reset();
            updateFileName(document.getElementById('file-input'));
            uploadSection.style.display = 'block';
        }
        
        function updateFileName(input) {
            const selectedFileInfo = document.getElementById('selected-file-info');
            const submitBtn = document.getElementById('submit-btn');
            
            if (input.files.length > 0) {
                selectedFileInfo.innerHTML = `<i class="fas fa-file-alt"></i> ${input.files[0].name}`;
                selectedFileInfo.style.display = 'block';
                submitBtn.disabled = false;
            } else {
                selectedFileInfo.style.display = 'none';
                submitBtn.disabled = true;
            }
        }
        
        const dropzone = document.getElementById('dropzone');
        dropzone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropzone.classList.add('bg-amber-100');
        });
        dropzone.addEventListener('dragleave', () => {
            dropzone.classList.remove('bg-amber-100');
        });
        dropzone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropzone.classList.remove('bg-amber-100');
            const fileInput = document.getElementById('file-input');
            fileInput.files = e.dataTransfer.files;
            updateFileName(fileInput);
        });

        function toggleSidebar() { /* Fungsi sidebar (jika diperlukan) */ }
    </script>
</body>
</html>