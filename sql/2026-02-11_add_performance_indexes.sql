-- PELITA performance indexes for complex dashboard/reporting queries
-- Date: 2026-02-11

ALTER TABLE buku_tamu
    ADD INDEX idx_year_keperluan_email (tahun, keperluan, email);

ALTER TABLE kepuasan
    ADD INDEX idx_email_period_rating (email, tahun, bulan, rating);
