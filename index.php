<?php
session_start();

// Redirect berdasarkan status login dan role
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'mahasiswa') {
        header('Location: mahasiswa/dashboard.php');
    } else {
        header('Location: asisten/dashboard.php');
    }
} else {
    header('Location: katalog.php');
}
exit();
?> 