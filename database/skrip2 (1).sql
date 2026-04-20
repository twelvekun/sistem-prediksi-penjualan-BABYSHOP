-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 20, 2026 at 08:14 AM
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
-- Database: `skrip2`
--

-- --------------------------------------------------------

--
-- Table structure for table `barangkeluar`
--

CREATE TABLE `barangkeluar` (
  `IDKELUAR` int(11) NOT NULL,
  `IDPRODUK` varchar(10) NOT NULL,
  `JUMLAHKELUAR` int(11) NOT NULL,
  `TUJUAN` varchar(255) DEFAULT NULL,
  `TANGGALKELUAR` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `barangmasuk`
--

CREATE TABLE `barangmasuk` (
  `IDMASUK` int(11) NOT NULL,
  `IDPEMASOK` int(11) DEFAULT NULL,
  `IDPRODUK` varchar(10) DEFAULT NULL,
  `TANGGALMASUK` date DEFAULT NULL,
  `JUMLAHMASUK` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kategori`
--

CREATE TABLE `kategori` (
  `IDKATEGORI` int(11) NOT NULL,
  `JENISPRODUK` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pemasok`
--

CREATE TABLE `pemasok` (
  `IDPEMASOK` int(11) NOT NULL,
  `NAMAPEMASOK` varchar(50) DEFAULT NULL,
  `KONTAK` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `penjualan`
--

CREATE TABLE `penjualan` (
  `IDJUAL` int(11) NOT NULL,
  `IDPRODUK` varchar(10) DEFAULT NULL,
  `TANGGALJUAL` date DEFAULT NULL,
  `JUMLAHJUAL` int(11) DEFAULT NULL,
  `HARI` int(2) DEFAULT NULL,
  `BULAN` int(2) DEFAULT NULL,
  `TAHUN` int(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pred`
--

CREATE TABLE `pred` (
  `IDPRED` int(11) NOT NULL,
  `WAKTUPRED` datetime DEFAULT NULL,
  `HASILPRED` varchar(20) DEFAULT NULL,
  `PREDIKSIBULANTAHUN` varchar(7) DEFAULT NULL,
  `NILAI_PREDIKSI` float DEFAULT NULL,
  `ALPHA` float DEFAULT NULL,
  `BETA` float DEFAULT NULL,
  `MAPE` float DEFAULT NULL,
  `AKURASI` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `produk`
--

CREATE TABLE `produk` (
  `IDPRODUK` varchar(10) NOT NULL,
  `IDKATEGORI` int(11) NOT NULL,
  `NAMAPRODUK` varchar(50) DEFAULT NULL,
  `JUMLAHPRODUK` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `IDUSER` int(11) NOT NULL,
  `USERNAME` varchar(50) DEFAULT NULL,
  `PASSWORD` varchar(50) DEFAULT NULL,
  `ROLE` enum('admin','user') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`IDUSER`, `USERNAME`, `PASSWORD`, `ROLE`) VALUES
(1, 'admin', 'admin', 'admin'),
(2, 'user1', 'user1', 'admin'),
(3, 'user2', 'user2p', 'user'),
(4, 'user', 'user', 'user'),
(5, 'Hans', 'Hans', 'admin');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `barangkeluar`
--
ALTER TABLE `barangkeluar`
  ADD PRIMARY KEY (`IDKELUAR`),
  ADD KEY `IDPRODUK` (`IDPRODUK`) USING BTREE;

--
-- Indexes for table `barangmasuk`
--
ALTER TABLE `barangmasuk`
  ADD PRIMARY KEY (`IDMASUK`),
  ADD KEY `IDPEMASOK` (`IDPEMASOK`),
  ADD KEY `IDPRODUK` (`IDPRODUK`);

--
-- Indexes for table `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`IDKATEGORI`);

--
-- Indexes for table `pemasok`
--
ALTER TABLE `pemasok`
  ADD PRIMARY KEY (`IDPEMASOK`);

--
-- Indexes for table `penjualan`
--
ALTER TABLE `penjualan`
  ADD PRIMARY KEY (`IDJUAL`),
  ADD KEY `IDPRODUK` (`IDPRODUK`);

--
-- Indexes for table `pred`
--
ALTER TABLE `pred`
  ADD PRIMARY KEY (`IDPRED`);

--
-- Indexes for table `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`IDPRODUK`),
  ADD KEY `IDKATEGORI` (`IDKATEGORI`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`IDUSER`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `barangkeluar`
--
ALTER TABLE `barangkeluar`
  MODIFY `IDKELUAR` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `barangmasuk`
--
ALTER TABLE `barangmasuk`
  MODIFY `IDMASUK` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `penjualan`
--
ALTER TABLE `penjualan`
  MODIFY `IDJUAL` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=391;

--
-- AUTO_INCREMENT for table `pred`
--
ALTER TABLE `pred`
  MODIFY `IDPRED` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `IDUSER` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `barangkeluar`
--
ALTER TABLE `barangkeluar`
  ADD CONSTRAINT `barangkeluar_ibfk_1` FOREIGN KEY (`IDPRODUK`) REFERENCES `produk` (`IDPRODUK`);

--
-- Constraints for table `barangmasuk`
--
ALTER TABLE `barangmasuk`
  ADD CONSTRAINT `barangmasuk_ibfk_1` FOREIGN KEY (`IDPEMASOK`) REFERENCES `pemasok` (`IDPEMASOK`),
  ADD CONSTRAINT `barangmasuk_ibfk_2` FOREIGN KEY (`IDPRODUK`) REFERENCES `produk` (`IDPRODUK`);

--
-- Constraints for table `penjualan`
--
ALTER TABLE `penjualan`
  ADD CONSTRAINT `penjualan_ibfk_1` FOREIGN KEY (`IDPRODUK`) REFERENCES `produk` (`IDPRODUK`);

--
-- Constraints for table `produk`
--
ALTER TABLE `produk`
  ADD CONSTRAINT `produk_ibfk_1` FOREIGN KEY (`IDKATEGORI`) REFERENCES `kategori` (`IDKATEGORI`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
