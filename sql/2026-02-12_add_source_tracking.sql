-- ============================================
-- Migration: Add source tracking for conflict detection
-- Run on REMOTE MySQL hosting only
-- ============================================

-- Buku Tamu: Add source tracking columns
ALTER TABLE `buku_tamu` ADD COLUMN `source_id` INT UNSIGNED DEFAULT NULL COMMENT 'ID dari database lokal asal';
ALTER TABLE `buku_tamu` ADD COLUMN `source_hash` VARCHAR(64) DEFAULT NULL COMMENT 'SHA-256 hash data untuk deteksi duplikat';
ALTER TABLE `buku_tamu` ADD UNIQUE INDEX `idx_source_unique` (`source_id`, `source_hash`);

-- Kepuasan: Add source tracking columns
ALTER TABLE `kepuasan` ADD COLUMN `source_id` INT UNSIGNED DEFAULT NULL COMMENT 'ID dari database lokal asal';
ALTER TABLE `kepuasan` ADD COLUMN `source_hash` VARCHAR(64) DEFAULT NULL COMMENT 'SHA-256 hash data untuk deteksi duplikat';
ALTER TABLE `kepuasan` ADD UNIQUE INDEX `idx_source_unique` (`source_id`, `source_hash`);
