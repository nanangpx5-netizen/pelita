# Panduan Sinkronisasi Database (Hybrid Offline-Online)

Fitur ini memungkinkan aplikasi PELITA berjalan di jaringan lokal (Offline/LAN) untuk performa maksimal, namun data tetap tersinkronisasi ke server Hosting (Cloud) secara otomatis.

## 1. Arsitektur

*   **Local App (Front Office)**: Aplikasi berjalan di komputer resepsionis menggunakan Laragon/XAMPP. Database utama adalah database lokal.
*   **Sync Worker**: Skrip background yang berjalan otomatis memeriksa data baru di lokal dan mengirimnya ke cloud.
*   **Cloud Hosting**: Hanya berfungsi sebagai tempat penyimpanan data terpusat (Data Warehouse) atau untuk dashboard pimpinan yang bisa diakses dari mana saja.

## 2. Persiapan Database Hosting

Agar skrip lokal bisa mengirim data ke hosting, Anda perlu mengizinkan **Remote MySQL Connection**:

1.  Login ke **cPanel** hosting Anda.
2.  Cari menu **Remote MySQL**.
3.  Tambahkan IP Address kantor Anda (atau gunakan `%` untuk mengizinkan dari mana saja - *kurang aman, hati-hati*).
4.  Pastikan User Database di hosting memiliki hak akses penuh (INSERT, SELECT).

## 3. Konfigurasi Aplikasi Lokal

Buka file `.env` di komputer lokal (`c:\laragon\www\pelita\.env`) dan tambahkan konfigurasi database remote:

```ini
# Database Lokal (XAMPP/Laragon)
DB_HOST=localhost
DB_PORT=3306
DB_NAME=pelita
DB_USER=root
DB_PASS=

# Database Remote (Hosting)
REMOTE_DB_HOST=ip_address_hosting_atau_domain
REMOTE_DB_PORT=3306
REMOTE_DB_NAME=u12345_pelita_prod
REMOTE_DB_USER=u12345_admin
REMOTE_DB_PASS=password_rahasia
```

## 4. Cara Menjalankan Sinkronisasi

### Cara Manual (Untuk Tes)
Buka terminal dan jalankan:
```bash
php scripts/sync_data.php
```
Jika berhasil, akan muncul pesan jumlah data yang dikirim.

### Cara Otomatis (Windows Task Scheduler)
Agar sinkronisasi berjalan otomatis setiap menit:

1.  Buka **Task Scheduler** di Windows.
2.  Pilih **Create Basic Task**.
3.  Nama: `Pelita Data Sync`.
4.  Trigger: **Daily**, lalu ubah properties nanti menjadi "Repeat every 1 minute".
5.  Action: **Start a program**.
    *   **Program/script**: `C:\laragon\bin\php\php-8.1.10-Win32-vs16-x64\php.exe` (Sesuaikan path PHP Laragon Anda).
    *   **Add arguments**: `C:\laragon\www\pelita\scripts\sync_data.php`
    *   **Start in**: `C:\laragon\www\pelita\scripts`
6.  Setelah selesai, klik kanan task tersebut -> **Properties** -> **Triggers** -> Edit -> Centang **Repeat task every:** pilih **5 minutes** (atau ganti manual jadi 1 minute) -> **for a duration of: Indefinitely**.

## 5. Catatan Penting
*   **One-Way Sync**: Saat ini sinkronisasi hanya berjalan satu arah (Lokal -> Cloud). Data yang diedit di Cloud tidak akan turun ke Lokal.
*   **Konflik ID**: Data di Cloud akan memiliki ID baru (auto-increment) yang mungkin berbeda dengan ID lokal. Ini normal.
*   **Internet Mati**: Jika internet mati, data akan menumpuk di lokal. Saat internet nyala kembali, skrip akan otomatis mengirim semua data yang tertunda.
