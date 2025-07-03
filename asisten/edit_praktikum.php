<?php
session_start();
require_once '../config.php';

// Cek apakah user sudah login dan role-nya asisten
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'asisten') {
    header('Location: ../login.php');
    exit();
}

// Cek apakah ada POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    $kode_mk = trim($_POST['kode_mk']);
    $nama_mk = trim($_POST['nama_mk']);
    $deskripsi = trim($_POST['deskripsi']);
    
    // Validasi input
    if (empty($kode_mk) || empty($nama_mk)) {
        $_SESSION['error'] = "Kode mata kuliah dan nama mata praktikum harus diisi.";
        header('Location: kelola_praktikum.php');
        exit();
    }
    
    // Cek apakah praktikum exists
    $check_sql = "SELECT id FROM mata_praktikum WHERE id = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $_SESSION['error'] = "Mata praktikum tidak ditemukan.";
        header('Location: kelola_praktikum.php');
        exit();
    }
    
    // Cek apakah kode mata kuliah sudah ada (kecuali untuk praktikum yang sedang diedit)
    $check_kode_sql = "SELECT id FROM mata_praktikum WHERE kode_mk = ? AND id != ?";
    $stmt = $conn->prepare($check_kode_sql);
    $stmt->bind_param("si", $kode_mk, $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $_SESSION['error'] = "Kode mata kuliah sudah digunakan.";
        header('Location: kelola_praktikum.php');
        exit();
    }
    
    // Update praktikum
    $update_sql = "UPDATE mata_praktikum SET kode_mk = ?, nama_mk = ?, deskripsi = ? WHERE id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("sssi", $kode_mk, $nama_mk, $deskripsi, $id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Mata praktikum berhasil diperbarui: " . $nama_mk;
    } else {
        $_SESSION['error'] = "Gagal memperbarui mata praktikum. Silakan coba lagi.";
    }
} else {
    $_SESSION['error'] = "Metode request tidak valid.";
}

header('Location: kelola_praktikum.php');
exit();
?> 