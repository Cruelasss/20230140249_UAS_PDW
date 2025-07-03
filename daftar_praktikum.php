<?php
session_start();
require_once 'config.php';

// Cek apakah user sudah login dan role-nya mahasiswa
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header('Location: login.php');
    exit();
}

// Cek apakah ada POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['praktikum_id'])) {
    $praktikum_id = (int)$_POST['praktikum_id'];
    $mahasiswa_id = $_SESSION['user_id'];
    
    // Validasi praktikum exists
    $check_praktikum = "SELECT id, nama_mk FROM mata_praktikum WHERE id = ?";
    $stmt = $conn->prepare($check_praktikum);
    $stmt->bind_param("i", $praktikum_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $_SESSION['error'] = "Mata praktikum tidak ditemukan.";
        header('Location: katalog.php');
        exit();
    }
    
    $praktikum = $result->fetch_assoc();
    
    // Cek apakah sudah terdaftar
    $check_duplicate = "SELECT id FROM pendaftaran_praktikum WHERE mahasiswa_id = ? AND praktikum_id = ?";
    $stmt = $conn->prepare($check_duplicate);
    $stmt->bind_param("ii", $mahasiswa_id, $praktikum_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $_SESSION['error'] = "Anda sudah terdaftar pada mata praktikum ini.";
        header('Location: katalog.php');
        exit();
    }
    
    // Insert pendaftaran
    $insert_sql = "INSERT INTO pendaftaran_praktikum (mahasiswa_id, praktikum_id) VALUES (?, ?)";
    $stmt = $conn->prepare($insert_sql);
    $stmt->bind_param("ii", $mahasiswa_id, $praktikum_id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Berhasil mendaftar pada mata praktikum: " . $praktikum['nama_mk'];
        header('Location: mahasiswa/dashboard.php');
        exit();
    } else {
        $_SESSION['error'] = "Gagal mendaftar. Silakan coba lagi.";
        header('Location: katalog.php');
        exit();
    }
} else {
    // Jika bukan POST request, redirect ke katalog
    header('Location: katalog.php');
    exit();
}
?> 