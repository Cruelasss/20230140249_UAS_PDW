<?php
session_start();
require_once '../config.php';

// Cek apakah user sudah login dan role-nya asisten
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'asisten') {
    header('Location: ../login.php');
    exit();
}

// Cek apakah ada POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode_mk = trim($_POST['kode_mk']);
    $nama_mk = trim($_POST['nama_mk']);
    $deskripsi = trim($_POST['deskripsi']);
    
    // Validasi input
    if (empty($kode_mk) || empty($nama_mk)) {
        $_SESSION['error'] = "Kode mata kuliah dan nama mata praktikum harus diisi.";
        header('Location: kelola_praktikum.php');
        exit();
    }
    
    // Cek apakah kode mata kuliah sudah ada
    $check_sql = "SELECT id FROM mata_praktikum WHERE kode_mk = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("s", $kode_mk);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $_SESSION['error'] = "Kode mata kuliah sudah digunakan.";
        header('Location: kelola_praktikum.php');
        exit();
    }
    
    // Insert praktikum baru
    $insert_sql = "INSERT INTO mata_praktikum (kode_mk, nama_mk, deskripsi, asisten_id) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_sql);
    $stmt->bind_param("sssi", $kode_mk, $nama_mk, $deskripsi, $_SESSION['user_id']);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Mata praktikum berhasil ditambahkan: " . $nama_mk;
    } else {
        $_SESSION['error'] = "Gagal menambahkan mata praktikum. Silakan coba lagi.";
    }
} else {
    $_SESSION['error'] = "Metode request tidak valid.";
}

header('Location: kelola_praktikum.php');
exit();
?> 