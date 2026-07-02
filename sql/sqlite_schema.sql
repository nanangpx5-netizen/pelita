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

-- ============================================
-- Table: ref_layanan (Antrian)
-- ============================================
CREATE TABLE IF NOT EXISTS `ref_layanan` (
    `id` INTEGER PRIMARY KEY AUTOINCREMENT,
    `kode` TEXT NOT NULL UNIQUE,
    `nama` TEXT NOT NULL,
    `deskripsi` TEXT DEFAULT NULL,
    `max_harian` INTEGER DEFAULT 100,
    `is_active` INTEGER DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
);

INSERT OR IGNORE INTO `ref_layanan` (`kode`, `nama`, `deskripsi`, `max_harian`) VALUES
('UMU', 'Pelayanan Umum', 'Pelayanan informasi dan konsultasi umum', 100),
('DMI', 'Data Mikro', 'Permintaan data mikro dan statistik daerah', 50),
('PUB', 'Penjualan Publikasi', 'Pembelian publikasi resmi BPS', 30),
('PRK', 'Perpustakaan', 'Akses perpustakaan dan referensi', 40),
('KNS', 'Konsultasi Statistik', 'Konsultasi metodologi dan analisis statistik', 20);

-- ============================================
-- Table: antrian
-- ============================================
CREATE TABLE IF NOT EXISTS `antrian` (
    `id` INTEGER PRIMARY KEY AUTOINCREMENT,
    `kode_layanan` TEXT NOT NULL,
    `nomor_urut` INTEGER NOT NULL,
    `tanggal` TEXT NOT NULL,
    `nomor_antrian` TEXT NOT NULL,
    `status` TEXT NOT NULL DEFAULT 'menunggu',
    `waktu_ambil` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `waktu_panggil` DATETIME DEFAULT NULL,
    `waktu_selesai` DATETIME DEFAULT NULL,
    `nama_pemohon` TEXT DEFAULT NULL,
    `nohp_pemohon` TEXT DEFAULT NULL,
    `catatan` TEXT DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS `idx_antrian_tanggal` ON `antrian` (`tanggal`);
CREATE INDEX IF NOT EXISTS `idx_antrian_status` ON `antrian` (`status`);
CREATE INDEX IF NOT EXISTS `idx_antrian_layanan_tanggal` ON `antrian` (`kode_layanan`, `tanggal`);

-- ============================================
-- Table: github_updates
-- ============================================
CREATE TABLE IF NOT EXISTS `github_updates` (
    `id` INTEGER PRIMARY KEY AUTOINCREMENT,
    `fetch_type` TEXT NOT NULL,
    `sha` TEXT DEFAULT NULL,
    `message` TEXT DEFAULT NULL,
    `author_name` TEXT DEFAULT NULL,
    `author_email` TEXT DEFAULT NULL,
    `author_username` TEXT DEFAULT NULL,
    `date` TEXT DEFAULT NULL,
    `url` TEXT DEFAULT NULL,
    `tag_name` TEXT DEFAULT NULL,
    `name` TEXT DEFAULT NULL,
    `body_preview` TEXT DEFAULT NULL,
    `prerelease` INTEGER DEFAULT 0,
    `draft` INTEGER DEFAULT 0,
    `published_at` TEXT DEFAULT NULL,
    `number` INTEGER DEFAULT NULL,
    `title` TEXT DEFAULT NULL,
    `state` TEXT DEFAULT NULL,
    `labels` TEXT DEFAULT NULL,
    `created_at` TEXT DEFAULT NULL,
    `updated_at` TEXT DEFAULT NULL,
    `comments` INTEGER DEFAULT 0,
    `merged_at` TEXT DEFAULT NULL,
    `head_branch` TEXT DEFAULT NULL,
    `base_branch` TEXT DEFAULT NULL,
    `user` TEXT DEFAULT NULL,
    `fetched_at` DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS `idx_github_fetch_type` ON `github_updates` (`fetch_type`);
CREATE INDEX IF NOT EXISTS `idx_github_fetched_at` ON `github_updates` (`fetched_at`);
