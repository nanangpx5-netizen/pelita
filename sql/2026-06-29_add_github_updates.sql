-- ============================================
-- Migration: Add GitHub updates tracking table
-- Version: 1.0.0
-- Berlaku untuk: MySQL & SQLite
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
CREATE INDEX IF NOT EXISTS `idx_github_date` ON `github_updates` (`date`);
