# Prosedur Backup & Recovery Database PELITA

## Prasyarat

- PHP CLI tersedia (`php -v`)
- Akses ke folder project PELITA

---

## 1. Backup Database Lokal (SQLite)

### Manual via CLI

```bash
php scripts/backup_db.php
```

**Output:**

- File backup: `backups/pelita_sqlite_YYYY-MM-DD_HHiiss.sqlite`
- Metadata: `backups/pelita_sqlite_YYYY-MM-DD_HHiiss.sqlite.meta.json`

### Otomatis (Windows Task Scheduler)

```
Program: php
Arguments: C:\laragon\www\pelita\scripts\backup_db.php
Schedule: Daily pukul 15:30
```

### Otomatis (Linux Cron)

```cron
30 15 * * * cd /var/www/pelita && php scripts/backup_db.php >> logs/backup.log 2>&1
```

> Backup otomatis menyimpan max 30 file, sisanya dihapus otomatis.

---

## 2. Restore Database Lokal

### Lihat daftar backup

```bash
php scripts/restore_db.php --list
```

### Restore backup terakhir

```bash
php scripts/restore_db.php --latest
```

### Restore file tertentu

```bash
php scripts/restore_db.php --file=pelita_sqlite_2026-02-12_141259.sqlite
```

> **Catatan:** Sebelum restore, script **otomatis** membuat salinan database saat ini sebagai `pelita_pre_restore_*.sqlite`.

---

## 3. Backup Database Hosting (Remote MySQL)

### Via cPanel phpMyAdmin

1. Login ke `panel.bpsjember.my.id`
2. Buka phpMyAdmin → Database `bpsjembe_pelita`
3. Pilih menu **Export** → Quick → Format SQL → Go
4. Simpan file `.sql` di folder `backups/`

### Via CLI (di server hosting)

```bash
mysqldump -u bpsjembe_pelita -p bpsjembe_pelita > backups/remote_YYYY-MM-DD.sql
```

---

## 4. Recovery Strategy

| Skenario | Langkah |
|----------|---------|
| PC rusak | Install ulang PHP+Laragon → copy backup `.sqlite` ke `database/` → restore |
| Data corrupt | `php scripts/restore_db.php --latest` |
| Hosting down | Data lokal tetap aman di SQLite, sync ulang saat hosting kembali |
| Migrasi PC baru | Copy seluruh folder `pelita/` termasuk `database/*.sqlite` |

---

## 5. Verifikasi Backup

Setelah restore, cek integritas:

```bash
php scripts/sync_data.php --test
```

Pastikan jumlah record sesuai ekspektasi.
