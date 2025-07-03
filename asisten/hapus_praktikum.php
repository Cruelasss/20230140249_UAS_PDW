<?php
session_start();
require_once '../config.php';

// Cek apakah user sudah login dan role-nya asisten
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'asisten') {
    header('Location: ../login.php');
    exit();
}

// Cek apakah ada ID praktikum
if (!isset($_GET['id'])) {
    $_SESSION['error'] = "ID praktikum tidak ditemukan.";
    header('Location: kelola_praktikum.php');
    exit();
}

$praktikum_id = (int)$_GET['id'];

// Cek apakah praktikum exists
$check_sql = "SELECT id, nama_mk FROM mata_praktikum WHERE id = ?";
$stmt = $conn->prepare($check_sql);
$stmt->bind_param("i", $praktikum_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "Mata praktikum tidak ditemukan.";
    header('Location: kelola_praktikum.php');
    exit();
}

$praktikum = $result->fetch_assoc();

// Hapus praktikum (akan otomatis menghapus modul dan laporan karena CASCADE)
$delete_sql = "DELETE FROM mata_praktikum WHERE id = ?";
$stmt = $conn->prepare($delete_sql);
$stmt->bind_param("i", $praktikum_id);

if ($stmt->execute()) {
    $_SESSION['success'] = "Mata praktikum berhasil dihapus: " . $praktikum['nama_mk'];
} else {
    $_SESSION['error'] = "Gagal menghapus mata praktikum. Silakan coba lagi.";
}

header('Location: kelola_praktikum.php');
exit();
?> 