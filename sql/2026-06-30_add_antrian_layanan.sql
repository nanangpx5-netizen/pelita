-- ============================================
-- Migration: Add Layanan & Antrian Tables
-- Version: 1.0.0
-- Format Nomor Antrian: [Kode]-[Nomor Urut]-[YYMMDD]
-- Status: menunggu | dipanggil | selesai | batal
-- ============================================

-- Table: ref_layanan (jenis layanan yang memerlukan nomor antrian)
CREATE TABLE IF NOT EXISTS `ref_layanan` (
    `id` INTEGER PRIMARY KEY AUTOINCREMENT,
    `kode` TEXT NOT NULL UNIQUE,
    `nama` TEXT NOT NULL,
    `deskripsi` TEXT DEFAULT NULL,
    `max_harian` INTEGER DEFAULT 100,
    `is_active` INTEGER DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Seed default layanan
INSERT OR IGNORE INTO `ref_layanan` (`kode`, `nama`, `deskripsi`, `max_harian`) VALUES
('UMU', 'Pelayanan Umum', 'Pelayanan informasi dan konsultasi umum', 100),
('DMI', 'Data Mikro', 'Permintaan data mikro dan statistik daerah', 50),
('PUB', 'Penjualan Publikasi', 'Pembelian publikasi resmi BPS', 30),
('PRK', 'Perpustakaan', 'Akses perpustakaan dan referensi', 40),
('KNS', 'Konsultasi Statistik', 'Konsultasi metodologi dan analisis statistik', 20);

-- Table: antrian (nomor antrian layanan)
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
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`kode_layanan`) REFERENCES `ref_layanan`(`kode`)
);

CREATE INDEX IF NOT EXISTS `idx_antrian_tanggal` ON `antrian` (`tanggal`);
CREATE INDEX IF NOT EXISTS `idx_antrian_status` ON `antrian` (`status`);
CREATE INDEX IF NOT EXISTS `idx_antrian_layanan_tanggal` ON `antrian` (`kode_layanan`, `tanggal`);
CREATE INDEX IF NOT EXISTS `idx_antrian_nomor` ON `antrian` (`nomor_antrian`);
