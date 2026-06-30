-- ============================================
-- PELITA Database Schema
-- Version: 1.0.0
-- Author: BPS Kabupaten Jember
-- ============================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+07:00";

-- Database: pelita
CREATE DATABASE IF NOT EXISTS `pelita` 
DEFAULT CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE `pelita`;

-- ============================================
-- Table: admin
-- ============================================
DROP TABLE IF EXISTS `admin`;
CREATE TABLE `admin` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `username` VARCHAR(64) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `nama` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) DEFAULT NULL,
    `last_login` DATETIME DEFAULT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default admin (password: Admin@Pelita2026)
INSERT INTO `admin` (`username`, `password`, `nama`, `email`) VALUES
('admin_pelita', '$2y$10$28QtAbMOrGevUWjnytj7xerJoaWux8bAfIz4nkUziI7cZgsBIbZjm', 'Administrator PELITA', 'admin@bpsjember.go.id');

-- ============================================
-- Table: buku_tamu
-- ============================================
DROP TABLE IF EXISTS `buku_tamu`;
CREATE TABLE `buku_tamu` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `tahun` YEAR NOT NULL,
    `bulan` CHAR(2) NOT NULL,
    `hari` CHAR(2) NOT NULL,
    `waktu` TIME NOT NULL,
    `nama` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `alamat` TEXT NOT NULL,
    `nohp` VARCHAR(15) NOT NULL,
    `umur` TINYINT UNSIGNED NOT NULL,
    `asal` VARCHAR(150) NOT NULL,
    `jenis_kelamin` ENUM('Laki-laki', 'Perempuan') NOT NULL,
    `pendidikan` VARCHAR(50) NOT NULL,
    `pekerjaan` VARCHAR(50) NOT NULL,
    `keperluan` VARCHAR(150) NOT NULL,
    `keperluan_lain` VARCHAR(150) DEFAULT NULL,
    `nomor_antrian` VARCHAR(10) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_tanggal` (`tahun`, `bulan`, `hari`),
    INDEX `idx_keperluan` (`keperluan`),
    INDEX `idx_year_keperluan_email` (`tahun`, `keperluan`, `email`),
    INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: kepuasan
-- ============================================
DROP TABLE IF EXISTS `kepuasan`;
CREATE TABLE `kepuasan` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `tahun` YEAR NOT NULL,
    `bulan` CHAR(2) NOT NULL,
    `hari` CHAR(2) NOT NULL,
    `waktu` TIME NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `rating` ENUM('Sangat Puas', 'Puas', 'Kurang Puas') NOT NULL,
    `komentar` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_tanggal` (`tahun`, `bulan`, `hari`),
    INDEX `idx_rating` (`rating`),
    INDEX `idx_email_period_rating` (`email`, `tahun`, `bulan`, `rating`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: ref_bulan
-- ============================================
DROP TABLE IF EXISTS `ref_bulan`;
CREATE TABLE `ref_bulan` (
    `id` TINYINT UNSIGNED NOT NULL,
    `nama` VARCHAR(20) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `ref_bulan` (`id`, `nama`) VALUES 
(1, 'Januari'), (2, 'Februari'), (3, 'Maret'), (4, 'April'),
(5, 'Mei'), (6, 'Juni'), (7, 'Juli'), (8, 'Agustus'),
(9, 'September'), (10, 'Oktober'), (11, 'November'), (12, 'Desember');

-- ============================================
-- Table: ref_pendidikan
-- ============================================
DROP TABLE IF EXISTS `ref_pendidikan`;
CREATE TABLE `ref_pendidikan` (
    `id` TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nama` VARCHAR(30) NOT NULL,
    `urutan` TINYINT UNSIGNED NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `ref_pendidikan` (`nama`, `urutan`) VALUES 
('SD', 1), ('SMP', 2), ('SMA/SMK', 3), ('D1/D2/D3', 4), 
('D4/S1', 5), ('S2', 6), ('S3', 7);

-- ============================================
-- Table: ref_pekerjaan
-- ============================================
DROP TABLE IF EXISTS `ref_pekerjaan`;
CREATE TABLE `ref_pekerjaan` (
    `id` TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nama` VARCHAR(50) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `ref_pekerjaan` (`nama`) VALUES 
('Belum Bekerja'), ('Mahasiswa'), ('PNS'), ('TNI/Polri'), 
('Guru/Dosen'), ('Karyawan Swasta'), ('Karyawan BUMN'), 
('Wiraswasta'), ('Lainnya');

-- ============================================
-- Table: ref_keperluan
-- ============================================
DROP TABLE IF EXISTS `ref_keperluan`;
CREATE TABLE `ref_keperluan` (
    `id` TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nama` VARCHAR(100) NOT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `ref_keperluan` (`nama`) VALUES 
('Perpustakaan Tercetak'), ('Perpustakaan Digital'), 
('Penjualan Publikasi'), ('Konsultasi Statistik'), 
('Data Mikro'), ('Rekomendasi Kegiatan Statistik'), ('Lainnya');

-- ============================================
-- Table: log_activity (Optional - Audit Trail)
-- ============================================
DROP TABLE IF EXISTS `log_activity`;
CREATE TABLE `log_activity` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `admin_id` INT(11) UNSIGNED DEFAULT NULL,
    `action` VARCHAR(50) NOT NULL,
    `table_name` VARCHAR(50) NOT NULL,
    `record_id` INT(11) UNSIGNED DEFAULT NULL,
    `old_data` JSON DEFAULT NULL,
    `new_data` JSON DEFAULT NULL,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `user_agent` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_admin` (`admin_id`),
    INDEX `idx_action` (`action`),
    INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
