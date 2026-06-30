-- ============================================
-- PELITA Database Schema (SQLite Version)
-- Version: 1.1.0
-- ============================================

-- Table: admin
CREATE TABLE IF NOT EXISTS `admin` (
    `id` INTEGER PRIMARY KEY AUTOINCREMENT,
    `username` TEXT NOT NULL UNIQUE,
    `password` TEXT NOT NULL,
    `nama` TEXT NOT NULL,
    `email` TEXT DEFAULT NULL,
    `last_login` DATETIME DEFAULT NULL,
    `is_active` INTEGER DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Default admin (password: Admin@Pelita2026)
INSERT OR IGNORE INTO `admin` (`username`, `password`, `nama`, `email`) VALUES
('admin_pelita', '$2y$10$28QtAbMOrGevUWjnytj7xerJoaWux8bAfIz4nkUziI7cZgsBIbZjm', 'Administrator PELITA', 'admin@bpsjember.go.id');

-- Table: buku_tamu
CREATE TABLE IF NOT EXISTS `buku_tamu` (
    `id` INTEGER PRIMARY KEY AUTOINCREMENT,
    `tahun` INTEGER NOT NULL,
    `bulan` TEXT NOT NULL,
    `hari` TEXT NOT NULL,
    `waktu` TEXT NOT NULL,
    `nama` TEXT NOT NULL,
    `email` TEXT NOT NULL,
    `alamat` TEXT NOT NULL,
    `nohp` TEXT NOT NULL,
    `umur` INTEGER NOT NULL,
    `asal` TEXT NOT NULL,
    `jenis_kelamin` TEXT CHECK(`jenis_kelamin` IN ('Laki-laki', 'Perempuan')) NOT NULL,
    `pendidikan` TEXT NOT NULL,
    `pekerjaan` TEXT NOT NULL,
    `keperluan` TEXT NOT NULL,
    `keperluan_lain` TEXT DEFAULT NULL,
    `nomor_antrian` TEXT NOT NULL,
    `synced_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS `idx_tanggal` ON `buku_tamu` (`tahun`, `bulan`, `hari`);
CREATE INDEX IF NOT EXISTS `idx_keperluan` ON `buku_tamu` (`keperluan`);
CREATE INDEX IF NOT EXISTS `idx_created` ON `buku_tamu` (`created_at`);

-- Table: kepuasan
CREATE TABLE IF NOT EXISTS `kepuasan` (
    `id` INTEGER PRIMARY KEY AUTOINCREMENT,
    `tahun` INTEGER NOT NULL,
    `bulan` TEXT NOT NULL,
    `hari` TEXT NOT NULL,
    `waktu` TEXT NOT NULL,
    `email` TEXT NOT NULL,
    `rating` TEXT CHECK(`rating` IN ('Sangat Puas', 'Puas', 'Kurang Puas')) NOT NULL,
    `komentar` TEXT DEFAULT NULL,
    `synced_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS `idx_tanggal_kepuasan` ON `kepuasan` (`tahun`, `bulan`, `hari`);

-- Table: ref_bulan
CREATE TABLE IF NOT EXISTS `ref_bulan` (
    `id` INTEGER PRIMARY KEY,
    `nama` TEXT NOT NULL
);

INSERT OR IGNORE INTO `ref_bulan` (`id`, `nama`) VALUES 
(1, 'Januari'), (2, 'Februari'), (3, 'Maret'), (4, 'April'),
(5, 'Mei'), (6, 'Juni'), (7, 'Juli'), (8, 'Agustus'),
(9, 'September'), (10, 'Oktober'), (11, 'November'), (12, 'Desember');

-- Table: ref_pendidikan
CREATE TABLE IF NOT EXISTS `ref_pendidikan` (
    `id` INTEGER PRIMARY KEY AUTOINCREMENT,
    `nama` TEXT NOT NULL,
    `urutan` INTEGER NOT NULL
);

INSERT OR IGNORE INTO `ref_pendidikan` (`nama`, `urutan`) VALUES 
('SD', 1), ('SMP', 2), ('SMA/SMK', 3), ('D1/D2/D3', 4), 
('D4/S1', 5), ('S2', 6), ('S3', 7);

-- Table: ref_pekerjaan
CREATE TABLE IF NOT EXISTS `ref_pekerjaan` (
    `id` INTEGER PRIMARY KEY AUTOINCREMENT,
    `nama` TEXT NOT NULL
);

INSERT OR IGNORE INTO `ref_pekerjaan` (`nama`) VALUES 
('Belum Bekerja'), ('Mahasiswa'), ('PNS'), ('TNI/Polri'), 
('Guru/Dosen'), ('Karyawan Swasta'), ('Karyawan BUMN'), 
('Wiraswasta'), ('Lainnya');

-- Table: ref_keperluan
CREATE TABLE IF NOT EXISTS `ref_keperluan` (
    `id` INTEGER PRIMARY KEY AUTOINCREMENT,
    `nama` TEXT NOT NULL,
    `is_active` INTEGER DEFAULT 1
);

INSERT OR IGNORE INTO `ref_keperluan` (`nama`) VALUES 
('Perpustakaan Tercetak'), ('Perpustakaan Digital'), 
('Penjualan Publikasi'), ('Konsultasi Statistik'), 
('Data Mikro'), ('Rekomendasi Kegiatan Statistik'), ('Lainnya');
