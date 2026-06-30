ALTER TABLE `buku_tamu` ADD COLUMN `synced_at` DATETIME DEFAULT NULL AFTER `created_at`;
ALTER TABLE `buku_tamu` ADD INDEX `idx_synced_at` (`synced_at`);

ALTER TABLE `kepuasan` ADD COLUMN `synced_at` DATETIME DEFAULT NULL AFTER `created_at`;
ALTER TABLE `kepuasan` ADD INDEX `idx_synced_at` (`synced_at`);
