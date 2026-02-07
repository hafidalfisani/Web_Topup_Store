-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 09, 2025 at 09:37 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_topup_game`
--

-- --------------------------------------------------------

--
-- Table structure for table `pembayaran`
--

CREATE TABLE `pembayaran` (
  `id_pembayaran` int(11) NOT NULL,
  `id_transaksi` int(11) NOT NULL,
  `metode` varchar(50) NOT NULL,
  `jumlah` double NOT NULL,
  `tanggal_bayar` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pembayaran`
--

INSERT INTO `pembayaran` (`id_pembayaran`, `id_transaksi`, `metode`, `jumlah`, `tanggal_bayar`) VALUES
(1, 2, 'DANA', 40000, '2025-11-16 21:33:12'),
(2, 3, 'GoPay', 15000, '2025-11-17 09:16:52'),
(3, 4, 'Smartfren', 750000, '2025-11-17 09:34:15'),
(4, 5, 'DANA', 15000, '2025-11-17 17:32:06'),
(5, 6, 'DANA', 15000, '2025-11-17 17:36:37'),
(6, 7, 'DANA', 15000, '2025-11-17 17:42:21'),
(7, 8, 'GoPay', 15000, '2025-11-17 17:48:52'),
(8, 9, 'OVO', 15000, '2025-11-17 17:51:21'),
(9, 10, 'OVO', 15000, '2025-11-17 17:55:02'),
(10, 11, 'XL Axiata', 30000, '2025-11-17 17:58:55'),
(11, 12, 'Smartfren', 750000, '2025-11-17 18:04:47'),
(12, 13, 'OVO', 15000, '2025-11-17 18:12:54'),
(13, 14, 'DANA', 30000, '2025-11-17 18:23:56'),
(14, 15, 'DANA', 30000, '2025-11-17 18:30:43'),
(15, 16, 'OVO', 30000, '2025-11-18 17:56:34'),
(16, 17, 'OVO', 40000, '2025-11-18 18:21:52'),
(17, 18, 'XL Axiata', 15000, '2025-11-18 18:36:24'),
(18, 19, 'DANA', 30000, '2025-11-18 21:20:41'),
(19, 20, 'DANA', 30000, '2025-11-18 21:31:05'),
(20, 21, 'DANA', 30000, '2025-11-18 21:39:18'),
(21, 22, 'OVO', 30000, '2025-11-18 21:58:35'),
(22, 23, 'DANA', 30000, '2025-11-18 22:00:06'),
(23, 24, 'GoPay', 15000, '2025-11-18 22:01:40'),
(24, 25, 'OVO', 30000, '2025-11-18 22:07:50'),
(25, 26, 'OVO', 15000, '2025-11-18 22:09:05'),
(26, 27, 'XL Axiata', 30000, '2025-11-18 22:09:34'),
(27, 28, 'OVO', 30000, '2025-11-18 22:10:30'),
(28, 29, 'OVO', 30000, '2025-11-18 22:19:15'),
(29, 30, 'GoPay', 15000, '2025-11-18 22:27:55'),
(30, 31, 'GoPay', 15000, '2025-11-20 11:50:44'),
(31, 32, 'OVO', 15000, '2025-11-20 11:55:08'),
(32, 33, 'DANA', 150000, '2025-11-20 12:02:51'),
(33, 34, 'Smartfren', 150000, '2025-11-20 12:05:42'),
(34, 36, 'Gopay', 0, '2025-11-20 12:28:59'),
(35, 37, 'OVO', 0, '2025-11-20 12:31:46'),
(36, 38, 'OVO', 0, '2025-11-20 12:34:15'),
(37, 39, 'DANA', 0, '2025-11-20 13:28:26'),
(38, 40, 'DANA', 0, '2025-11-20 13:51:37'),
(39, 41, 'DANA', 0, '2025-11-20 14:10:09'),
(40, 42, 'DANA', 0, '2025-11-20 14:12:52'),
(41, 43, 'Transfer BCA', 0, '2025-11-20 14:19:01'),
(42, 45, 'Gopay', 0, '2025-11-20 14:21:13'),
(43, 46, 'Transfer Mandiri', 0, '2025-11-20 14:22:13'),
(44, 47, 'DANA', 0, '2025-11-21 20:35:11'),
(45, 48, 'DANA', 0, '2025-11-22 09:46:30'),
(46, 49, 'DANA', 0, '2025-11-22 12:10:38'),
(47, 50, 'Transfer Mandiri', 0, '2025-11-22 12:24:17'),
(48, 51, 'OVO', 0, '2025-11-23 06:58:19'),
(49, 52, 'DANA', 0, '2025-11-23 07:47:55'),
(50, 53, 'Transfer BCA', 0, '2025-11-23 10:40:18'),
(51, 54, 'Transfer BCA', 0, '2025-11-23 10:52:27'),
(52, 55, 'DANA', 0, '2025-11-24 14:14:33'),
(53, 56, 'Transfer BCA', 0, '2025-11-26 12:33:47'),
(54, 57, 'OVO', 0, '2025-11-26 15:52:19'),
(55, 58, 'DANA', 0, '2025-12-09 13:38:02'),
(56, 59, 'OVO', 0, '2025-12-09 13:41:23');

-- --------------------------------------------------------

--
-- Table structure for table `produk`
--

CREATE TABLE `produk` (
  `id_produk` int(11) NOT NULL,
  `nama_produk` varchar(100) NOT NULL,
  `gambar_produk` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `produk`
--

INSERT INTO `produk` (`id_produk`, `nama_produk`, `gambar_produk`) VALUES
(1, 'Diamond Free Fire', 'ff.jpg'),
(2, 'Voucher AOV Top-Up', 'aov.jpg'),
(3, 'PUBG Mobile UC', 'pubg.jpg'),
(4, 'Mobile Legends Diamond', 'ml.jpg'),
(5, 'Genshin Impact Genesis Crystal', 'genshin.jpg'),
(6, 'Honkai: Star Rail Oneiric Shard', 'hok.jpg'),
(7, 'Roblox Robux', 'roblox.jpg'),
(8, 'FC Mobile Points', 'fcmobile.jpg'),
(9, 'Clash of Clans Gems', 'coc.jpg'),
(109, 'Point Blank PB Cash', 'pointblank.jpg'),
(110, 'Apex Legends Apex Coins', 'apex.jpg'),
(111, 'Call of Duty Mobile CP', 'codm.jpg'),
(112, 'Valorant VP (Valorant Points)', 'valorant.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `produk_tier`
--

CREATE TABLE `produk_tier` (
  `id_tier` int(11) NOT NULL,
  `id_produk` int(11) NOT NULL,
  `jumlah_jenis_uang` varchar(50) NOT NULL,
  `harga` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `produk_tier`
--

INSERT INTO `produk_tier` (`id_tier`, `id_produk`, `jumlah_jenis_uang`, `harga`) VALUES
(1, 1, '50 Diamond', 15000),
(2, 1, '100 Diamond', 30000),
(3, 2, '50 Voucher', 16000),
(4, 2, '100 Voucher', 32000),
(5, 3, '100 UC', 25000),
(6, 4, '86 Diamond', 20000),
(7, 5, '60 Genesis Crystal', 16000),
(8, 5, '300 + 30 Genesis Crystal', 79000),
(9, 6, '60 Oneiric Shard', 16000),
(10, 6, '300 + 30 Oneiric Shard', 79000),
(11, 7, '80 Robux', 15000),
(12, 7, '275 Robux', 48000),
(13, 8, '100 FC Points', 20000),
(14, 8, '500 FC Points', 95000),
(15, 9, '80 Gems', 15000),
(16, 9, '300 Gems', 48000),
(17, 9, '500 Gems', 75000),
(18, 9, '1200 Gems', 170000);

-- --------------------------------------------------------

--
-- Table structure for table `transaksi`
--

CREATE TABLE `transaksi` (
  `id_transaksi` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `id_produk` int(11) NOT NULL,
  `tanggal` datetime NOT NULL DEFAULT current_timestamp(),
  `total_harga` double NOT NULL,
  `status` varchar(50) NOT NULL,
  `player_id` varchar(50) DEFAULT NULL,
  `tier_beli` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transaksi`
--

INSERT INTO `transaksi` (`id_transaksi`, `id_user`, `id_produk`, `tanggal`, `total_harga`, `status`, `player_id`, `tier_beli`) VALUES
(2, 2, 8, '0000-00-00 00:00:00', 40000, '0', '62793845', '140 Diamond'),
(3, 2, 1, '0000-00-00 00:00:00', 15000, '0', '62793845', '50 Diamond'),
(4, 2, 4, '0000-00-00 00:00:00', 750000, '0', '23421234', '3640 Diamond'),
(5, 2, 1, '0000-00-00 00:00:00', 15000, '0', '62793845', '50 Diamond'),
(6, 2, 1, '0000-00-00 00:00:00', 15000, '0', '23421234', '50 Diamond'),
(7, 2, 1, '0000-00-00 00:00:00', 15000, '0', '62793845', '50 Diamond'),
(8, 2, 4, '0000-00-00 00:00:00', 15000, '0', '62793845', '50 Diamond'),
(9, 2, 8, '0000-00-00 00:00:00', 15000, '0', '23421234', '50 Diamond'),
(10, 2, 1, '0000-00-00 00:00:00', 15000, '0', '62793845', '50 Diamond'),
(11, 2, 1, '0000-00-00 00:00:00', 30000, '0', '62793845', '100 Diamond'),
(12, 2, 8, '0000-00-00 00:00:00', 750000, '0', '62793845', '3640 Diamond'),
(13, 2, 8, '0000-00-00 00:00:00', 15000, '0', '23421234', '50 Diamond'),
(14, 2, 8, '0000-00-00 00:00:00', 30000, '0', '23421234', '100 Diamond'),
(15, 2, 1, '0000-00-00 00:00:00', 30000, '0', '23421234', '100 Diamond'),
(16, 2, 4, '0000-00-00 00:00:00', 30000, '0', '62793845', '100 Diamond'),
(17, 2, 1, '0000-00-00 00:00:00', 40000, '0', '62793845', '140 Diamond'),
(18, 2, 8, '0000-00-00 00:00:00', 15000, '0', '62793845', '50 Diamond'),
(19, 2, 7, '0000-00-00 00:00:00', 30000, '0', '62793845', '100 Diamond'),
(20, 2, 7, '0000-00-00 00:00:00', 30000, '0', '62793845', '100 Diamond'),
(21, 2, 7, '0000-00-00 00:00:00', 30000, '0', '23421234', '100 Diamond'),
(22, 2, 1, '0000-00-00 00:00:00', 30000, '0', '62793845', '100 Diamond'),
(23, 2, 8, '0000-00-00 00:00:00', 30000, '0', '23421234', '100 Diamond'),
(24, 2, 8, '0000-00-00 00:00:00', 15000, '0', '23421234', '50 Diamond'),
(25, 2, 1, '0000-00-00 00:00:00', 30000, '0', '23421234', '100 Diamond'),
(26, 2, 1, '0000-00-00 00:00:00', 15000, '0', '23421234', '50 Diamond'),
(27, 2, 1, '0000-00-00 00:00:00', 30000, '0', '23421234', '100 Diamond'),
(28, 2, 1, '0000-00-00 00:00:00', 30000, '0', '23421234', '100 Diamond'),
(29, 2, 1, '0000-00-00 00:00:00', 30000, '0', '23421234', '100 Diamond'),
(30, 2, 1, '0000-00-00 00:00:00', 15000, '0', '23421234', '50 Diamond'),
(31, 2, 4, '0000-00-00 00:00:00', 15000, '0', '23421234', '50 Diamond'),
(32, 2, 1, '0000-00-00 00:00:00', 15000, '0', '23421234', '50 Diamond'),
(33, 2, 8, '0000-00-00 00:00:00', 150000, '0', '23421234', '720 Diamond'),
(34, 2, 8, '0000-00-00 00:00:00', 150000, '0', '23421234', '720 Diamond'),
(36, 2, 4, '2025-11-20 12:28:59', 15000, 'Rejected', '23421234', '50'),
(37, 2, 3, '2025-11-20 12:31:46', 15000, 'Rejected', '23421234', '50'),
(38, 2, 3, '2025-11-20 12:34:15', 25000, 'Rejected', '23421234', '100'),
(39, 2, 6, '2025-11-20 13:28:25', 50000, 'Success', '23421234', '250'),
(40, 2, 3, '2025-11-20 13:51:37', 15000, 'Rejected', '23421234', '50'),
(41, 2, 6, '2025-11-20 14:10:09', 15000, 'Success', '23421234', '50'),
(42, 2, 2, '2025-11-20 14:12:52', 15000, 'Success', '23421234', '50'),
(43, 2, 6, '2025-11-20 14:19:01', 15000, 'Success', '23421234', '50'),
(45, 2, 8, '2025-11-20 14:21:13', 15000, 'Rejected', '23421234', '50'),
(46, 2, 6, '2025-11-20 14:22:13', 95000, 'Success', '23421234', '500'),
(47, 2, 2, '2025-11-21 20:35:11', 15000, 'Success', '23421234', '50'),
(48, 2, 4, '2025-11-22 09:46:30', 25000, 'Ditolak', '23421234', '100'),
(49, 2, 2, '2025-11-22 12:10:38', 15000, 'Ditolak', '23421234', '50'),
(50, 2, 6, '2025-11-22 12:24:17', 95000, 'Ditolak', '23421234', '500'),
(51, 2, 2, '2025-11-23 06:58:19', 50000, 'Ditolak', '23421234', '250'),
(52, 2, 3, '2025-11-23 07:47:55', 25000, 'Ditolak', '23421234', '100'),
(53, 7, 2, '2025-11-23 10:40:18', 25000, 'Selesai', '23421234', '100'),
(54, 7, 3, '2025-11-23 10:52:27', 25000, 'Ditolak', '23421234', '100'),
(55, 7, 3, '2025-11-24 14:14:33', 95000, 'Selesai', '23421234', '500'),
(56, 7, 3, '2025-11-26 12:33:47', 25000, 'Selesai', '23421234', '100'),
(57, 7, 3, '2025-11-26 15:52:19', 340000, 'Selesai', '23421234', '2000'),
(58, 7, 2, '2025-12-09 13:38:02', 340000, 'Ditolak', '23421234', '2000'),
(59, 7, 4, '2025-12-09 13:41:23', 50000, 'Selesai', '23421234', '250');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id_user` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `saldo` double DEFAULT 0,
  `role` varchar(50) NOT NULL DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id_user`, `nama`, `email`, `password`, `saldo`, `role`) VALUES
(2, 'aldi', 'aldi@gmail.com', '$2y$10$tM9sN8hX7vV0zQ2oR3p6x.5j4.k7.l9/H2vE1wP4D5gM0L9K8J7I', 0, 'user'),
(4, 'admin', 'admin@topup.com', '$2y$10$/LDI4mqh.w.aRmE6Yqz62.HCg4mEClVO5zEi5p.MLnIkhQTTW.vJm', 0, 'admin'),
(7, 'hafid', 'hafid@gmail.com', '$2y$10$thL2.xezjOiGd7LwrAMuAe3D8Ubsb/WQ1144boi5Tl61w0PKVZMSu', 0, 'user');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD PRIMARY KEY (`id_pembayaran`),
  ADD KEY `id_transaksi` (`id_transaksi`);

--
-- Indexes for table `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`id_produk`);

--
-- Indexes for table `produk_tier`
--
ALTER TABLE `produk_tier`
  ADD PRIMARY KEY (`id_tier`),
  ADD KEY `id_produk` (`id_produk`);

--
-- Indexes for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id_transaksi`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_produk` (`id_produk`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `pembayaran`
--
ALTER TABLE `pembayaran`
  MODIFY `id_pembayaran` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `produk`
--
ALTER TABLE `produk`
  MODIFY `id_produk` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=113;

--
-- AUTO_INCREMENT for table `produk_tier`
--
ALTER TABLE `produk_tier`
  MODIFY `id_tier` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id_transaksi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD CONSTRAINT `pembayaran_ibfk_1` FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi` (`id_transaksi`);

--
-- Constraints for table `produk_tier`
--
ALTER TABLE `produk_tier`
  ADD CONSTRAINT `produk_tier_ibfk_1` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD CONSTRAINT `transaksi_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`),
  ADD CONSTRAINT `transaksi_ibfk_2` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
