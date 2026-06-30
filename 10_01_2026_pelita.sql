/*
 Navicat Premium Dump SQL

 Source Server         : laragon
 Source Server Type    : MySQL
 Source Server Version : 80030 (8.0.30)
 Source Host           : localhost:3306
 Source Schema         : pelita

 Target Server Type    : MySQL
 Target Server Version : 80030 (8.0.30)
 File Encoding         : 65001

 Date: 10/01/2026 07:45:57
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for admin
-- ----------------------------
DROP TABLE IF EXISTS `admin`;
CREATE TABLE `admin`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `last_login` datetime NULL DEFAULT NULL,
  `is_active` tinyint(1) NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `unique_username`(`username` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of admin
-- ----------------------------
INSERT INTO `admin` VALUES (1, 'admin_pelita', '$2y$10$28QtAbMOrGevUWjnytj7xerJoaWux8bAfIz4nkUziI7cZgsBIbZjm', 'Administrator PELITA', 'admin@bpsjember.go.id', '2026-01-08 23:30:07', 1, '2026-01-06 16:20:43', '2026-01-08 23:30:07');

-- ----------------------------
-- Table structure for buku_tamu
-- ----------------------------
DROP TABLE IF EXISTS `buku_tamu`;
CREATE TABLE `buku_tamu`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `tahun` year NOT NULL,
  `bulan` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `hari` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `waktu` time NOT NULL,
  `nama` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '',
  `alamat` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `nohp` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `umur` tinyint UNSIGNED NULL DEFAULT 0,
  `asal` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `jenis_kelamin` enum('Laki-laki','Perempuan') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `pendidikan` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '-',
  `pekerjaan` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '-',
  `keperluan` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `keperluan_lain` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `nomor_antrian` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_tanggal`(`tahun` ASC, `bulan` ASC, `hari` ASC) USING BTREE,
  INDEX `idx_keperluan`(`keperluan` ASC) USING BTREE,
  INDEX `idx_created`(`created_at` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of buku_tamu
-- ----------------------------

-- ----------------------------
-- Table structure for kepuasan
-- ----------------------------
DROP TABLE IF EXISTS `kepuasan`;
CREATE TABLE `kepuasan`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `tahun` year NOT NULL,
  `bulan` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `hari` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `waktu` time NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '',
  `rating` enum('Sangat Puas','Puas','Kurang Puas') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `komentar` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_tanggal`(`tahun` ASC, `bulan` ASC, `hari` ASC) USING BTREE,
  INDEX `idx_rating`(`rating` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of kepuasan
-- ----------------------------

-- ----------------------------
-- Table structure for log_activity
-- ----------------------------
DROP TABLE IF EXISTS `log_activity`;
CREATE TABLE `log_activity`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `admin_id` int UNSIGNED NULL DEFAULT NULL,
  `action` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `table_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `record_id` int UNSIGNED NULL DEFAULT NULL,
  `old_data` json NULL,
  `new_data` json NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_admin`(`admin_id` ASC) USING BTREE,
  INDEX `idx_action`(`action` ASC) USING BTREE,
  INDEX `idx_created`(`created_at` ASC) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of log_activity
-- ----------------------------

-- ----------------------------
-- Table structure for ref_bulan
-- ----------------------------
DROP TABLE IF EXISTS `ref_bulan`;
CREATE TABLE `ref_bulan`  (
  `id` tinyint UNSIGNED NOT NULL,
  `nama` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of ref_bulan
-- ----------------------------
INSERT INTO `ref_bulan` VALUES (1, 'Januari');
INSERT INTO `ref_bulan` VALUES (2, 'Februari');
INSERT INTO `ref_bulan` VALUES (3, 'Maret');
INSERT INTO `ref_bulan` VALUES (4, 'April');
INSERT INTO `ref_bulan` VALUES (5, 'Mei');
INSERT INTO `ref_bulan` VALUES (6, 'Juni');
INSERT INTO `ref_bulan` VALUES (7, 'Juli');
INSERT INTO `ref_bulan` VALUES (8, 'Agustus');
INSERT INTO `ref_bulan` VALUES (9, 'September');
INSERT INTO `ref_bulan` VALUES (10, 'Oktober');
INSERT INTO `ref_bulan` VALUES (11, 'November');
INSERT INTO `ref_bulan` VALUES (12, 'Desember');

-- ----------------------------
-- Table structure for ref_keperluan
-- ----------------------------
DROP TABLE IF EXISTS `ref_keperluan`;
CREATE TABLE `ref_keperluan`  (
  `id` tinyint UNSIGNED NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NULL DEFAULT 1,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 8 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of ref_keperluan
-- ----------------------------
INSERT INTO `ref_keperluan` VALUES (1, 'Perpustakaan Tercetak', 1);
INSERT INTO `ref_keperluan` VALUES (2, 'Perpustakaan Digital', 1);
INSERT INTO `ref_keperluan` VALUES (3, 'Penjualan Publikasi', 1);
INSERT INTO `ref_keperluan` VALUES (4, 'Konsultasi Statistik', 1);
INSERT INTO `ref_keperluan` VALUES (5, 'Data Mikro', 1);
INSERT INTO `ref_keperluan` VALUES (6, 'Rekomendasi Kegiatan Statistik', 1);
INSERT INTO `ref_keperluan` VALUES (7, 'Lainnya', 1);

-- ----------------------------
-- Table structure for ref_pekerjaan
-- ----------------------------
DROP TABLE IF EXISTS `ref_pekerjaan`;
CREATE TABLE `ref_pekerjaan`  (
  `id` tinyint UNSIGNED NOT NULL AUTO_INCREMENT,
  `nama` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 10 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of ref_pekerjaan
-- ----------------------------
INSERT INTO `ref_pekerjaan` VALUES (1, 'Belum Bekerja');
INSERT INTO `ref_pekerjaan` VALUES (2, 'Mahasiswa');
INSERT INTO `ref_pekerjaan` VALUES (3, 'PNS');
INSERT INTO `ref_pekerjaan` VALUES (4, 'TNI/Polri');
INSERT INTO `ref_pekerjaan` VALUES (5, 'Guru/Dosen');
INSERT INTO `ref_pekerjaan` VALUES (6, 'Karyawan Swasta');
INSERT INTO `ref_pekerjaan` VALUES (7, 'Karyawan BUMN');
INSERT INTO `ref_pekerjaan` VALUES (8, 'Wiraswasta');
INSERT INTO `ref_pekerjaan` VALUES (9, 'Lainnya');

-- ----------------------------
-- Table structure for ref_pendidikan
-- ----------------------------
DROP TABLE IF EXISTS `ref_pendidikan`;
CREATE TABLE `ref_pendidikan`  (
  `id` tinyint UNSIGNED NOT NULL AUTO_INCREMENT,
  `nama` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `urutan` tinyint UNSIGNED NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 8 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of ref_pendidikan
-- ----------------------------
INSERT INTO `ref_pendidikan` VALUES (1, 'SD', 1);
INSERT INTO `ref_pendidikan` VALUES (2, 'SMP', 2);
INSERT INTO `ref_pendidikan` VALUES (3, 'SMA/SMK', 3);
INSERT INTO `ref_pendidikan` VALUES (4, 'D1/D2/D3', 4);
INSERT INTO `ref_pendidikan` VALUES (5, 'D4/S1', 5);
INSERT INTO `ref_pendidikan` VALUES (6, 'S2', 6);
INSERT INTO `ref_pendidikan` VALUES (7, 'S3', 7);

SET FOREIGN_KEY_CHECKS = 1;
