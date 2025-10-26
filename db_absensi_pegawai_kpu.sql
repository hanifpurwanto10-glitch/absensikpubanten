-- phpMyAdmin SQL Dump
-- version 5.1.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 26 Okt 2025 pada 09.53
-- Versi server: 10.4.22-MariaDB
-- Versi PHP: 7.4.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_absensi_pegawai_kpu`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `absensi`
--

CREATE TABLE `absensi` (
  `id` bigint(20) NOT NULL,
  `user_id` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `jam_masuk` time DEFAULT NULL,
  `jam_keluar` time DEFAULT NULL,
  `foto_masuk` text DEFAULT NULL COMMENT 'Disimpan sebagai base64 string atau path file',
  `foto_keluar` text DEFAULT NULL,
  `lokasi_masuk_lat` varchar(100) DEFAULT NULL,
  `lokasi_masuk_lng` varchar(100) DEFAULT NULL,
  `lokasi_keluar_lat` varchar(100) DEFAULT NULL,
  `lokasi_keluar_lng` varchar(100) DEFAULT NULL,
  `status_masuk` enum('tepat_waktu','terlambat','alpha','izin','sakit','cuti') DEFAULT NULL,
  `status_keluar` enum('sesuai','pulang_cepat','lembur') DEFAULT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `laporan_kerja` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Tabel untuk menyimpan data absensi harian';

--
-- Dumping data untuk tabel `absensi`
--

INSERT INTO `absensi` (`id`, `user_id`, `tanggal`, `jam_masuk`, `jam_keluar`, `foto_masuk`, `foto_keluar`, `lokasi_masuk_lat`, `lokasi_masuk_lng`, `lokasi_keluar_lat`, `lokasi_keluar_lng`, `status_masuk`, `status_keluar`, `keterangan`, `laporan_kerja`) VALUES
(7, 8, '2025-10-26', '01:39:11', '01:39:20', '68fd194f74ec5_20251026_013911.jpg', '68fd1958771da_20251026_013920.jpg', '-6.151133', '106.182277', '-6.151133', '106.182277', 'tepat_waktu', 'sesuai', NULL, NULL),
(8, 5, '2025-10-26', '01:39:38', '01:39:44', '68fd196a06cc6_20251026_013938.jpg', '68fd1970858f0_20251026_013944.jpg', '-6.151133', '106.182277', '-6.151133', '106.182277', 'tepat_waktu', 'sesuai', NULL, NULL),
(9, 6, '2025-10-26', '01:48:24', '01:49:09', '68fd1b78d6838_20251026_014824.jpg', '68fd1ba565610_20251026_014909.jpg', '-6.1511653747207', '106.18226320555', '-6.1511653747207', '106.18226320555', 'tepat_waktu', 'sesuai', NULL, NULL),
(10, 7, '2025-10-26', '01:58:12', '01:58:21', '68fd1dc4dd8b1_20251026_015812.jpg', '68fd1dcdc24c9_20251026_015821.jpg', '-6.151172', '106.182268', '-6.151172', '106.182268', 'tepat_waktu', 'sesuai', NULL, NULL),
(11, 9, '2025-10-26', '02:18:55', '02:19:15', '68fd229fd72bb_20251026_021855.jpg', '68fd22b38ad39_20251026_021915.jpg', '-6.1511653747207', '106.18226320555', '-6.1511653747207', '106.18226320555', 'tepat_waktu', 'sesuai', NULL, NULL),
(12, 11, '2025-10-26', '02:24:49', '02:24:57', '68fd2401e4887_20251026_022449.jpg', '68fd240934754_20251026_022457.jpg', '-6.151172', '106.182268', '-6.151172', '106.182268', 'tepat_waktu', 'sesuai', NULL, 'ke KPPN untuk mengurus kenaikan gaji sekeretaris kpu provinsi hanif purwanto'),
(13, 12, '2025-10-26', '02:25:42', NULL, '68fd24362707a_20251026_022542.jpg', NULL, '-6.151172', '106.182268', NULL, NULL, 'tepat_waktu', NULL, NULL, NULL),
(14, 10, '2025-10-26', '02:28:16', NULL, '68fd24d0accec_20251026_022816.jpg', NULL, '-6.151172', '106.182268', NULL, NULL, 'tepat_waktu', NULL, NULL, NULL),
(15, 13, '2025-10-26', '02:29:29', NULL, '68fd25198482a_20251026_022929.jpg', NULL, '-6.151172', '106.182268', NULL, NULL, 'tepat_waktu', NULL, NULL, NULL),
(16, 15, '2025-10-26', '13:56:44', '13:58:28', '68fdc62c87d0f_20251026_135644.jpg', '68fdc694b9c24_20251026_135828.jpg', '-6.1515438', '106.1827652', '-6.1515434', '106.1827652', 'terlambat', 'sesuai', NULL, 'lapor1'),
(17, 16, '2025-10-26', '14:43:08', NULL, '68fdd10cd8f00_20251026_144308.jpg', NULL, '-6.1515385', '106.1827648', NULL, NULL, 'terlambat', NULL, NULL, 'membuar rilis media');

-- --------------------------------------------------------

--
-- Struktur dari tabel `izin_cuti`
--

CREATE TABLE `izin_cuti` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL COMMENT 'Pegawai yang mengajukan',
  `jenis` enum('izin','sakit','cuti') NOT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date NOT NULL,
  `alasan` text NOT NULL,
  `file_pendukung` varchar(255) DEFAULT NULL COMMENT 'Path ke file surat dokter, dll',
  `status_approval` enum('pending','disetujui','ditolak') NOT NULL DEFAULT 'pending',
  `approved_by` int(11) DEFAULT NULL COMMENT 'ID Supervisor/Admin yang menyetujui',
  `tanggal_pengajuan` timestamp NULL DEFAULT current_timestamp(),
  `tanggal_approval` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Tabel untuk pengajuan izin, sakit, dan cuti';

-- --------------------------------------------------------

--
-- Struktur dari tabel `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL COMMENT 'Kunci pengaturan (misal: jam_masuk)',
  `setting_value` varchar(255) NOT NULL COMMENT 'Nilai pengaturan (misal: 07:30:00)',
  `keterangan` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Tabel untuk pengaturan global aplikasi absensi';

--
-- Dumping data untuk tabel `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `keterangan`) VALUES
(1, 'jam_masuk', '07:30:00', 'Jam masuk kerja resmi (Senin-Jumat)'),
(2, 'jam_pulang', '16:00:00', 'Jam pulang kerja resmi (Senin-Jumat)'),
(3, 'toleransi_menit', '15', 'Toleransi keterlambatan dalam menit'),
(4, 'lokasi_kantor_lat', '-6.118611', 'Latitude kantor KPU Banten (Contoh: Kota Serang)'),
(5, 'lokasi_kantor_lng', '106.150833', 'Longitude kantor KPU Banten (Contoh: Kota Serang)'),
(6, 'radius_kantor_meter', '300', 'Radius absensi yang diizinkan dari titik kantor (dalam meter)');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nip` varchar(30) NOT NULL COMMENT 'Nomor Induk Pegawai',
  `nama_lengkap` varchar(150) NOT NULL,
  `jabatan` varchar(100) DEFAULT NULL,
  `divisi` varchar(100) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL COMMENT 'Di-hash menggunakan bcrypt',
  `foto_profil` varchar(255) DEFAULT 'default.png',
  `role` enum('admin','supervisor','pegawai') NOT NULL DEFAULT 'pegawai',
  `status` enum('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Tabel untuk data pengguna (pegawai, supervisor, admin)';

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `nip`, `nama_lengkap`, `jabatan`, `divisi`, `email`, `password`, `foto_profil`, `role`, `status`, `created_at`, `updated_at`) VALUES
(1, 'ADMIN001', 'Admin Utama', 'Administrator', 'IT & Data', 'admin@123.com', 'admin123', 'default.png', 'admin', 'aktif', '2025-10-25 14:01:56', '2025-10-26 06:25:14'),
(2, 'SPV001', 'Dr. Supervisor, M.Si', 'Kepala Bagian Umum', 'Umum & Logistik', 'supervisor@kpu.go.id', '$2y$10$o8.jQ.EhlYpA3o5/qPz7keplL4evcO0iSNNpITz0q0B7OIQj9eJOW', 'default.png', 'supervisor', 'aktif', '2025-10-25 14:01:56', '2025-10-25 14:01:56'),
(3, 'PEG001', 'Pegawai Staf', 'Staf Pelaksana', 'Umum & Logistik', 'pegawai@kpu.go.id', '$2y$10$o8.jQ.EhlYpA3o5/qPz7keplL4evcO0iSNNpITz0q0B7OIQj9eJOW', 'default.png', 'pegawai', 'aktif', '2025-10-25 14:01:56', '2025-10-25 14:01:56'),
(5, '197402211994121001', 'hanif', NULL, NULL, 'hanif@gmail.com', '$2y$10$9O090vY9eS0uAce0Hv.A2.5zqWBP3fDTnf64jmJd1cHahQVQbPOx.', 'default.png', 'pegawai', 'aktif', '2025-10-25 15:24:34', '2025-10-25 15:24:34'),
(6, '123', 'ninik', NULL, NULL, 'ninik@gmail.com', '$2y$10$AVq.dBMfIOwD3EDFw39kTe8B7JqDZNqWBL2uBWfngxfHhe6Fq4KYe', 'default.png', 'pegawai', 'aktif', '2025-10-25 16:22:43', '2025-10-25 16:22:43'),
(7, '1', 'rio', NULL, NULL, 'rio@gmail.com', '$2y$10$hWYlfwecRRSrmKw8lKQIfeUAn10ntrBXELdx.pHqenJmEZCmRpX/G', 'default.png', 'pegawai', 'aktif', '2025-10-25 17:03:54', '2025-10-25 17:03:54'),
(8, '2', 'nasya', NULL, NULL, 'nasya@gmail.com', '$2y$10$.TimacypVw3pdveROPkqUeb564PusQ2XO9neIwb3R5/ZLB9fch2Ba', 'default.png', 'pegawai', 'aktif', '2025-10-25 18:03:55', '2025-10-25 18:03:55'),
(9, '6', 'dimas', NULL, NULL, 'dimas@gmail.com', '$2y$10$H8dNZnOZxRpgqkmhGgqlYeAPxYsJjeji24Va3SCkeMeoCtWnaIE.S', 'default.png', 'pegawai', 'aktif', '2025-10-25 19:10:14', '2025-10-25 19:10:14'),
(10, '7', 'indy', NULL, NULL, 'indy@gmail.com', '$2y$10$lZOY9N9KvuDdaXS6GSJY2eTLCk1OiwLHNFIlm4eg1OV.G0M5RW1G.', 'default.png', 'pegawai', 'aktif', '2025-10-25 19:10:46', '2025-10-25 19:10:46'),
(11, '8', 'corey', NULL, NULL, 'corey@gmail.com', '$2y$10$f/3YhQry8fzTRXE0lsFWeuY0hgwFrigANJCHgBZFQ6rhaSwM5X0Pu', 'default.png', 'pegawai', 'aktif', '2025-10-25 19:11:12', '2025-10-25 19:11:12'),
(12, '9', 'yudi', NULL, NULL, 'yudi@gmail.com', '$2y$10$v00QVozpcP5LAHcTQxii8.LQouZ83ma/ZrYuE.OwaJCzIHrSgYf7C', 'default.png', 'pegawai', 'aktif', '2025-10-25 19:11:37', '2025-10-25 19:11:37'),
(13, '10', 'iyan', NULL, NULL, 'iyan@gmail.com', '$2y$10$wgn9UGEvis3qIBJfARFuPeGteLYLf.K00Rzp0VnS9Q1JEfMYbTAhm', 'default.png', 'pegawai', 'aktif', '2025-10-25 19:12:07', '2025-10-25 19:12:07'),
(14, '11', 'edy', NULL, NULL, 'edy@gmail.com', '$2y$10$ep2rF/tBZpw09UZvoLOwquf0la2tBLPRv4Vy.R46ZuFFfSgL5P4pO', 'default.png', 'pegawai', 'aktif', '2025-10-26 05:45:48', '2025-10-26 05:45:48'),
(15, '12', 'danag', NULL, NULL, 'danang@gmail.com', '$2y$10$n/zGsfGu5sMKBe6ZwZ9Byu8U4W16wI.BlutcV6JA9/QssZv/BqX9K', 'default.png', 'pegawai', 'aktif', '2025-10-26 05:46:36', '2025-10-26 05:46:36'),
(16, '13', 'erlin', NULL, NULL, 'erlin@gmail.com', '$2y$10$jDfrN0EJtMh.UUESNxafluA0miJnmriYTkoRZ5sjR1.bhbwVE59TK', 'default.png', 'pegawai', 'aktif', '2025-10-26 05:47:11', '2025-10-26 05:47:11'),
(17, '14', 'faisal', NULL, NULL, 'faisal@gmail.com', '$2y$10$gbz8wtDZXETzXWStrw2k3.7sUGuh4zYqDHRvezdbo2Cp8ymPAnOya', 'default.png', 'pegawai', 'aktif', '2025-10-26 05:47:44', '2025-10-26 05:47:44'),
(18, '15', 'ica', NULL, NULL, 'ica@gmail.com', '$2y$10$HZWUct4MtAmCOvbyU/TILOXt/jUVbY6Q3LPlTVpZIBCrT2hAvxL/a', 'default.png', 'pegawai', 'aktif', '2025-10-26 05:48:13', '2025-10-26 05:48:13');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `absensi`
--
ALTER TABLE `absensi`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_tanggal_unique` (`user_id`,`tanggal`) COMMENT 'Satu user hanya bisa absen sekali per hari',
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_tanggal` (`tanggal`);

--
-- Indeks untuk tabel `izin_cuti`
--
ALTER TABLE `izin_cuti`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_izin_user_id` (`user_id`),
  ADD KEY `idx_approved_by` (`approved_by`),
  ADD KEY `idx_status_approval` (`status_approval`);

--
-- Indeks untuk tabel `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key_unique` (`setting_key`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nip_unique` (`nip`),
  ADD UNIQUE KEY `email_unique` (`email`),
  ADD KEY `idx_role` (`role`),
  ADD KEY `idx_divisi` (`divisi`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `absensi`
--
ALTER TABLE `absensi`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT untuk tabel `izin_cuti`
--
ALTER TABLE `izin_cuti`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `absensi`
--
ALTER TABLE `absensi`
  ADD CONSTRAINT `fk_absensi_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `izin_cuti`
--
ALTER TABLE `izin_cuti`
  ADD CONSTRAINT `fk_izin_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_izin_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
