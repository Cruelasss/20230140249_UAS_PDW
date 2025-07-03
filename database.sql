CREATE DATABASE IF NOT EXISTS `simpraktikum_db`;
USE `simpraktikum_db`;

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('mahasiswa','asisten') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `mata_praktikum` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `kode_mk` VARCHAR(20) NOT NULL UNIQUE,
  `nama_mk` VARCHAR(255) NOT NULL,
  `deskripsi` TEXT,
  `asisten_id` INT, -- ID asisten yang mengampu (opsional)
  FOREIGN KEY (`asisten_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `pendaftaran_praktikum` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `mahasiswa_id` INT NOT NULL,
  `praktikum_id` INT NOT NULL,
  `tgl_daftar` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`mahasiswa_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`praktikum_id`) REFERENCES `mata_praktikum`(`id`) ON DELETE CASCADE,
  UNIQUE(`mahasiswa_id`, `praktikum_id`) -- Memastikan mahasiswa hanya bisa mendaftar sekali per praktikum
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `modul` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `praktikum_id` INT NOT NULL,
  `judul_modul` VARCHAR(255) NOT NULL,
  `deskripsi_modul` TEXT,
  `file_materi` VARCHAR(255), -- Nama file materi yang diupload
  FOREIGN KEY (`praktikum_id`) REFERENCES `mata_praktikum`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `laporan` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `modul_id` INT NOT NULL,
  `mahasiswa_id` INT NOT NULL,
  `file_laporan` VARCHAR(255) NOT NULL, -- Nama file laporan yang diupload
  `tgl_kumpul` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `nilai` INT, -- Nilai bisa NULL jika belum dinilai
  `feedback` TEXT, -- Feedback dari asisten
  FOREIGN KEY (`modul_id`) REFERENCES `modul`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`mahasiswa_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- Sample data untuk testing SIMPRAK

-- Insert sample users (password: 123456)
INSERT INTO users (nama, email, password, role) VALUES
('Admin Asisten', 'admin@simprak.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'asisten'),
('Budi Santoso', 'budi@student.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mahasiswa'),
('Citra Lestari', 'citra@student.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mahasiswa'),
('Dewi Putri', 'dewi@student.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mahasiswa');

-- Insert sample mata praktikum
INSERT INTO mata_praktikum (kode_mk, nama_mk, deskripsi, asisten_id) VALUES
('PW001', 'Pemrograman Web', 'Mata praktikum yang mempelajari dasar-dasar pemrograman web menggunakan HTML, CSS, dan JavaScript.', 1),
('JK002', 'Jaringan Komputer', 'Mata praktikum yang mempelajari konsep dasar jaringan komputer dan protokol komunikasi.', 1),
('BD003', 'Basis Data', 'Mata praktikum yang mempelajari desain dan implementasi sistem basis data.', 1);

-- Insert sample modul
INSERT INTO modul (praktikum_id, judul_modul, deskripsi_modul, file_materi) VALUES
(1, 'Modul 1: HTML & CSS Dasar', 'Pengenalan HTML dan CSS untuk membuat halaman web sederhana.', 'modul1_html_css.pdf'),
(1, 'Modul 2: JavaScript Dasar', 'Pemrograman JavaScript untuk interaktivitas web.', 'modul2_javascript.pdf'),
(1, 'Modul 3: PHP Native', 'Pemrograman server-side dengan PHP.', 'modul3_php.pdf'),
(2, 'Modul 1: Konsep Jaringan', 'Pengenalan konsep dasar jaringan komputer.', 'modul1_jaringan.pdf'),
(2, 'Modul 2: Protokol TCP/IP', 'Mempelajari protokol TCP/IP dan implementasinya.', 'modul2_tcpip.pdf'),
(3, 'Modul 1: ERD & Normalisasi', 'Perancangan basis data dengan ERD dan normalisasi.', 'modul1_erd.pdf');

-- Insert sample pendaftaran praktikum
INSERT INTO pendaftaran_praktikum (mahasiswa_id, praktikum_id) VALUES
(2, 1), -- Budi mendaftar Pemrograman Web
(2, 2), -- Budi mendaftar Jaringan Komputer
(3, 1), -- Citra mendaftar Pemrograman Web
(4, 3); -- Dewi mendaftar Basis Data

-- Insert sample laporan
INSERT INTO laporan (modul_id, mahasiswa_id, file_laporan, nilai, feedback) VALUES
(1, 2, 'budi_modul1_laporan.pdf', 85, 'Laporan sangat baik, implementasi HTML dan CSS sudah sesuai standar.'),
(2, 2, 'budi_modul2_laporan.pdf', 90, 'Implementasi JavaScript sangat kreatif dan fungsional.'),
(1, 3, 'citra_modul1_laporan.pdf', NULL, NULL), -- Belum dinilai
(4, 2, 'budi_jaringan_modul1.pdf', 88, 'Pemahaman konsep jaringan sudah baik.');