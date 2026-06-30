# PELITA - Knowledge Transfer Document
## Complete System Understanding Guide for Developers

**Version:** 2.1.0  
**Date:** 11 February 2026  
**Target Audience:** Development Team  
**Prerequisites:** PHP 8.1+, MySQL 8.0+

---

## Table of Contents

1. [Quick Start Guide](#1-quick-start-guide)
2. [System Architecture Overview](#2-system-architecture-overview)
3. [Core Components Deep Dive](#3-core-components-deep-dive)
4. [Common Development Tasks](#4-common-development-tasks)
5. [Troubleshooting Guide](#5-troubleshooting-guide)
6. [Best Practices](#6-best-practices)
7. [Testing Guide](#7-testing-guide)
8. [Deployment Checklist](#8-deployment-checklist)

---

## 1. Quick Start Guide

### 1.1 Local Development Setup

```bash
# 1. Navigate to project directory
cd c:/laragon/www/pelita

# 2. Create database
mysql -u root -p
CREATE DATABASE pelita;
EXIT;

# 3. Import schema
mysql -u root -p pelita < sql/pelita.sql

# 4. Access application
# Open browser: http://localhost/pelita

# 5. Admin login
# URL: http://localhost/pelita/admin/login.php
# Username: admin_pelita
# Password: (check database or reset)
```

### 1.2 Reset Admin Password

```sql
-- Run in MySQL
USE pelita;
UPDATE admin 
SET password = '$2y$10$YourNewHashedPasswordHere' 
WHERE username = 'admin_pelita';

-- Or generate new hash in PHP:
<?php
echo password_hash('your_new_password', PASSWORD_DEFAULT);
?>
```

### 1.3 Key File Locations

| Component | Path | Purpose |
|-----------|------|---------|
| **App Config** | `config/app.php` | Application settings |
| **DB Config** | `config/database.php` | Database credentials |
| **Database Class** | `classes/Database.php` | Singleton DB connection |
| **BukuTamu Model** | `classes/BukuTamu.php` | Guest book logic |
| **Kepuasan Model** | `classes/Kepuasan.php` | Satisfaction logic |
| **Admin Model** | `classes/Admin.php` | Admin authentication |
| **Auth Handler** | `includes/auth.php` | Login/logout functions |
| **Helper Functions** | `includes/functions.php` | Utility functions |
| **CSRF Protection** | `includes/csrf.php` | Token generation/validation |
| **Landing Page** | `public/index.php` | Main public page |
| **Guest Form** | `public/buku-tamu.php` | Guest registration |
| **Satisfaction Form** | `public/kepuasan.php` | Satisfaction survey |
| **Admin Dashboard** | `admin/index.php` | Admin main page |
| **Admin Login** | `admin/login.php` | Admin authentication |

---

## 2. System Architecture Overview

### 2.1 Architecture Pattern

PELITA uses a **Monolithic MVC** pattern with **Native PHP OOP**:

```
┌─────────────────────────────────────────────────────────────┐
│                     Presentation Layer                        │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐      │
│  │   Public     │  │    Admin     │  │   Export     │      │
│  │   Pages      │  │   Panel      │  │   Handlers   │      │
│  └──────────────┘  └──────────────┘  └──────────────┘      │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│                     Business Logic Layer                     │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐      │
│  │  BukuTamu    │  │  Kepuasan    │  │    Admin     │      │
│  │   Model      │  │   Model      │  │   Model      │      │
│  └──────────────┘  └──────────────┘  └──────────────┘      │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐      │
│  │  Auth        │  │  CSRF        │  │  Functions   │      │
│  │  Handler     │  │  Protection  │  │  Helpers     │      │
│  └──────────────┘  └──────────────┘  └──────────────┘      │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│                      Data Access Layer                        │
│  ┌──────────────────────────────────────────────────────┐   │
│  │              Database (Singleton)                     │   │
│  │  - PDO Connection                                    │   │
│  │  - Prepared Statements                               │   │
│  │  - Query Builder Methods                             │   │
│  └──────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│                      Data Layer                              │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐  │
│  │   admin  │  │buku_tamu │  │ kepuasan │  │   ref_*  │  │
│  └──────────┘  └──────────┘  └──────────┘  └──────────┘  │
└─────────────────────────────────────────────────────────────┘
```

### 2.2 Request Flow

```
User Request
    ↓
Web Server (Apache/Nginx)
    ↓
PHP Router (.htaccess)
    ↓
Page Controller (index.php, admin/index.php, etc.)
    ↓
Authentication Check (require_login())
    ↓
Model Method (BukuTamu::getFiltered(), etc.)
    ↓
Database Query (Database::query())
    ↓
Data Processing
    ↓
View Rendering (HTML + Tailwind CSS)
    ↓
Response to User
```

### 2.3 Design Patterns Used

| Pattern | Implementation | File |
|---------|---------------|------|
| **Singleton** | Database connection | [`classes/Database.php`](classes/Database.php:10) |
| **Active Record** | Model classes | [`classes/BukuTamu.php`](classes/BukuTamu.php:8) |
| **Factory** | Session management | [`includes/auth.php`](includes/auth.php:15) |
| **Template Method** | Export handlers | `admin/buku-tamu/export-*.php` |
| **Facade** | Helper functions | [`includes/functions.php`](includes/functions.php:1) |

---

## 3. Core Components Deep Dive

### 3.1 Database Class (Singleton Pattern)

**Purpose:** Centralized database connection with PDO

**Key Methods:**
```php
// Get singleton instance
$db = Database::getInstance();

// Execute query with parameters
$result = $db->query("SELECT * FROM table WHERE id = :id", ['id' => 1]);

// Fetch single record
$record = $db->fetch("SELECT * FROM table WHERE id = :id", ['id' => 1]);

// Fetch all records
$records = $db->fetchAll("SELECT * FROM table");

// Insert record
$id = $db->insert('table', ['column' => 'value']);

// Update record
$affected = $db->update('table', ['column' => 'new_value'], "id = :id", ['id' => 1]);

// Delete record
$affected = $db->delete('table', "id = :id", ['id' => 1]);

// Count records
$count = $db->count('table', "status = :status", ['status' => 'active']);

// Transaction
$db->beginTransaction();
// ... operations ...
$db->commit(); // or $db->rollBack();
```

**Important Notes:**
- Always use prepared statements to prevent SQL injection
- The singleton ensures only one connection per request
- Query count is tracked via `getQueryCount()`

### 3.2 BukuTamu Model

**Purpose:** Manage guest book entries

**Key Methods:**
```php
$bukuTamu = new BukuTamu();

// Create new guest entry
$id = $bukuTamu->create([
    'nama' => 'John Doe',
    'nohp' => '08123456789',
    'asal' => 'Universitas Jember',
    'keperluan' => 'Konsultasi Statistik',
    'jenis_kelamin' => 'Laki-laki',
    'umur' => 25,
    'pendidikan' => 'S1',
    'pekerjaan' => 'Mahasiswa',
    'email' => 'john@example.com',
    'alamat' => 'Jl. Test No. 1'
]);

// Get filtered data with pagination
$data = $bukuTamu->getFiltered('01', '2026', 'John', 1, 20);
// Parameters: month, year, search, page, limit

// Get total count with filters
$total = $bukuTamu->getTotalFiltered('01', '2026', 'John');

// Get single record by ID
$record = $bukuTamu->getById($id);

// Get statistics
$stats = $bukuTamu->getStats();
// Returns: ['total', 'hari_ini', 'bulan_ini', 'tahun_ini']

// Get visit purpose statistics
$keperluanStats = $bukuTamu->getKeperluanStats('01', '2026');

// Get monthly trend
$trend = $bukuTamu->getMonthlyTrend(2026);

// Get data for export
$exportData = $bukuTamu->getForExport('01', '2026');

// Delete record
$bukuTamu->delete($id);
```

**Queue Number Algorithm:**
```php
// Resets daily, 3-digit zero-padded (001-999)
public function generateNomorAntrian(): string {
    $today = date('Y-m-d');
    $count = $this->db->count($this->table, "DATE(created_at) = :today", ['today' => $today]);
    return str_pad($count + 1, 3, '0', STR_PAD_LEFT);
}
```

### 3.3 Kepuasan Model

**Purpose:** Manage satisfaction surveys

**Key Methods:**
```php
$kepuasan = new Kepuasan();

// Create satisfaction entry
$id = $kepuasan->create('email@example.com', 'Sangat Puas', 'Excellent service!');

// Get filtered data with pagination
$data = $kepuasan->getFiltered('01', '2026', 'Sangat Puas', 1, 20);
// Parameters: month, year, rating, page, limit

// Get total count with filters
$total = $kepuasan->getTotalFiltered('01', '2026', 'Sangat Puas');

// Get satisfaction statistics
$stats = $kepuasan->getStats('01', '2026');
// Returns: ['Sangat Puas', 'Puas', 'Kurang Puas', 'total', 'persen_*']

// Get monthly trend
$trend = $kepuasan->getMonthlyTrend(2026);

// Get data for export
$exportData = $kepuasan->getForExport('01', '2026');
```

**Satisfaction Index Calculation:**
```php
// Weighted average: Sangat Puas=3, Puas=2, Kurang Puas=1
$indeks = (($stats['Sangat Puas'] * 3 + $stats['Puas'] * 2 + $stats['Kurang Puas'] * 1) / $stats['total']) / 3 * 100;
```

### 3.4 Admin Model

**Purpose:** Admin authentication and management

**Key Methods:**
```php
$admin = new Admin();

// Find admin by username
$user = $admin->findByUsername('admin_pelita');

// Find admin by ID
$user = $admin->findById(1);

// Update last login
$admin->updateLastLogin(1);

// Change password
$admin->changePassword(1, 'new_password');

// Verify password
$isValid = $admin->verifyPassword('input_password', $stored_hash);
```

### 3.5 Authentication Handler

**Purpose:** Session-based authentication

**Key Functions:**
```php
// Login user
$result = login('username', 'password');
// Returns: ['success' => true/false, 'message' => '...']

// Check if logged in
if (is_logged_in()) {
    // User is authenticated
}

// Get current admin data
$adminData = current_admin();

// Get admin ID
$id = admin_id();

// Get admin name
$name = admin_name();

// Require login (redirects if not logged in)
require_login();

// Logout user
logout();
```

### 3.6 Helper Functions

**Purpose:** Common utility functions

**Key Functions:**
```php
// Generate base URL
$url = base_url('admin/login.php');

// Generate asset URL
$assetUrl = asset_url('css/style.css');

// Sanitize input (XSS prevention)
$safe = sanitize('<script>alert("xss")</script>');

// Validate email
if (validate_email('test@example.com')) {
    // Valid email
}

// Validate Indonesian phone number
if (validate_phone('08123456789')) {
    // Valid phone
}

// Format date to Indonesian
$date = format_tanggal('2026-01-15', 'd F Y');
// Output: 15 Januari 2026

// Format datetime
$datetime = format_waktu('2026-01-15 14:30:45');
// Output: 15/01/2026 14:30

// Get month name
$month = get_nama_bulan(1);
// Output: Januari

// Flash message
flash('success', 'Operation successful!');
$message = flash('success'); // Retrieves and removes

// Check flash exists
if (has_flash('success')) {
    // Flash message exists
}

// Pagination
$pagination = paginate(100, 3, 20, '/admin/buku-tamu/');
// Returns: ['total', 'per_page', 'current_page', 'total_pages', 'has_prev', 'has_next', 'prev_url', 'next_url']

// Get client IP
$ip = get_client_ip();

// Get reference data
$months = get_ref_data('ref_bulan');
```

### 3.7 CSRF Protection

**Purpose:** Prevent Cross-Site Request Forgery

**Key Functions:**
```php
// Generate CSRF token
$token = csrf_token();

// Generate CSRF hidden field
$field = csrf_field();
// Output: <input type="hidden" name="csrf_token" value="...">

// Verify CSRF token
if (verify_csrf($_POST['csrf_token'])) {
    // Token is valid
}

// Validate CSRF from POST (with error handling)
if (validate_csrf()) {
    // Token is valid, proceed
} else {
    // Token is invalid, error message set
}
```

**Usage in Forms:**
```php
<form method="POST" action="">
    <?= csrf_field() ?>
    <!-- other form fields -->
    <button type="submit">Submit</button>
</form>
```

---

## 4. Common Development Tasks

### 4.1 Adding a New Form Field

**Step 1: Add column to database**
```sql
ALTER TABLE buku_tamu ADD COLUMN new_field VARCHAR(100) DEFAULT '';
```

**Step 2: Update form HTML**
```php
<!-- In public/buku-tamu.php -->
<div>
    <label class="form-label">New Field</label>
    <input type="text" name="new_field" value="<?= $_POST['new_field'] ?? '' ?>" class="form-input">
</div>
```

**Step 3: Update form processing**
```php
// In public/buku-tamu.php
$insertData = [
    // ... existing fields ...
    'new_field' => $data['new_field'] ?? ''
];
```

**Step 4: Update admin table**
```php
<!-- In admin/buku-tamu/index.php -->
<th>New Field</th>
<!-- ... -->
<td><?= sanitize($row['new_field']) ?></td>
```

### 4.2 Adding a New Reference Table

**Step 1: Create table**
```sql
CREATE TABLE ref_new_table (
    id TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
    nama VARCHAR(50) NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    PRIMARY KEY (id)
) ENGINE=InnoDB;
```

**Step 2: Insert reference data**
```sql
INSERT INTO ref_new_table (id, nama) VALUES
(1, 'Option 1'),
(2, 'Option 2'),
(3, 'Option 3');
```

**Step 3: Use in form**
```php
<?php
$options = get_ref_data('ref_new_table');
?>
<select name="new_field" class="form-input">
    <?php foreach ($options as $opt): ?>
        <option value="<?= $opt['nama'] ?>"><?= $opt['nama'] ?></option>
    <?php endforeach; ?>
</select>
```

### 4.3 Creating a New Export Format

**Step 1: Create export file**
```php
<?php
// admin/buku-tamu/export-csv.php
require_login();

$bukuTamu = new BukuTamu();
$data = $bukuTamu->getForExport($_GET['bulan'] ?? null, $_GET['tahun'] ?? date('Y'));

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="export.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['No', 'Nama', 'Asal', 'Keperluan']);

foreach ($data as $i => $row) {
    fputcsv($output, [
        $i + 1,
        $row['nama'],
        $row['asal'],
        $row['keperluan']
    ]);
}

fclose($output);
?>
```

**Step 2: Add export button**
```php
<a href="<?= base_url('admin/buku-tamu/export-csv.php') ?>?bulan=<?= $bulan ?>&tahun=<?= $tahun ?>" 
   class="btn btn-primary">
    Export CSV
</a>
```

### 4.4 Adding a New Admin Role

**Step 1: Add role column to admin table**
```sql
ALTER TABLE admin ADD COLUMN role ENUM('admin', 'operator', 'viewer') DEFAULT 'admin';
```

**Step 2: Update authentication check**
```php
// In includes/auth.php
function require_role($allowedRoles) {
    if (!is_logged_in()) {
        redirect('admin/login.php');
    }
    
    $admin = current_admin();
    if (!in_array($admin['role'], $allowedRoles)) {
        flash('error', 'Anda tidak memiliki akses');
        redirect('admin/');
    }
}

// Usage
require_role(['admin', 'operator']);
```

### 4.5 Implementing Search Functionality

```php
// In model class
public function search(string $keyword, int $page = 1, int $limit = ITEMS_PER_PAGE): array {
    $offset = ($page - 1) * $limit;
    
    $sql = "SELECT * FROM {$this->table} 
            WHERE nama LIKE :keyword 
               OR email LIKE :keyword 
               OR asal LIKE :keyword 
            ORDER BY id DESC 
            LIMIT {$limit} OFFSET {$offset}";
    
    return $this->db->fetchAll($sql, ['keyword' => "%{$keyword}%"]);
}

// In controller
$keyword = $_GET['search'] ?? '';
$data = $model->search($keyword, $page, ITEMS_PER_PAGE);
```

---

## 5. Troubleshooting Guide

### 5.1 Common Issues

| Issue | Cause | Solution |
|-------|-------|----------|
| **"SQLSTATE[HY093]: Invalid parameter number"** | Mismatch between placeholders and parameters | Check that array keys match `:placeholder` names in query |
| **"Class not found"** | Missing require statement | Add `require_once` for the class file |
| **CSRF token error** | Session expired or token mismatch | Refresh page or check session configuration |
| **Login fails** | Wrong password or inactive account | Check password hash and `is_active` flag |
| **Export shows blank page** | Output buffer issue | Remove any `echo` before headers |
| **CSS not loading** | CDN blocked or no internet | Check internet connection or use local CSS |
| **Database connection failed** | Wrong credentials or server down | Check `config/database.php` settings |

### 5.2 Debugging Tips

**Enable Error Reporting:**
```php
// In config/app.php (development only)
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

**Debug Database Queries:**
```php
// Log query count
$db = Database::getInstance();
// ... run queries ...
echo "Queries executed: " . $db->getQueryCount();
```

**Debug Session Data:**
```php
session_start();
var_dump($_SESSION);
```

**Debug POST Data:**
```php
var_dump($_POST);
```

### 5.3 Performance Issues

**Slow Queries:**
```sql
-- Check slow query log
SHOW VARIABLES LIKE 'slow_query_log%';

-- Analyze query with EXPLAIN
EXPLAIN SELECT * FROM buku_tamu WHERE nama LIKE '%test%';
```

**Optimization Tips:**
1. Add indexes on frequently queried columns
2. Use `LIMIT` for large result sets
3. Avoid `SELECT *` - specify columns
4. Use `COUNT(*)` instead of counting in PHP

---

## 6. Best Practices

### 6.1 Security Best Practices

1. **Always use prepared statements**
```php
// Good
$db->query("SELECT * FROM table WHERE id = :id", ['id' => $id]);

// Bad (SQL injection risk)
$db->query("SELECT * FROM table WHERE id = {$id}");
```

2. **Sanitize all output**
```php
// Good
echo sanitize($userInput);

// Bad (XSS risk)
echo $userInput;
```

3. **Validate CSRF tokens**
```php
// In all POST handlers
if (!validate_csrf()) {
    // Handle error
}
```

4. **Use password hashing**
```php
// Good
$hash = password_hash($password, PASSWORD_DEFAULT);

// Bad (plaintext storage)
$hash = $password;
```

5. **Check authentication**
```php
// In all admin pages
require_login();
```

### 6.2 Code Style

1. **Follow PSR-12 coding standard**
2. **Use camelCase for method names**
3. **Use snake_case for database columns**
4. **Add PHPDoc comments for classes and methods**
5. **Use meaningful variable names**

### 6.3 Database Best Practices

1. **Use appropriate data types**
2. **Add indexes on foreign keys and search columns**
3. **Use `DATETIME` for timestamps, not `VARCHAR`**
4. **Use `ENUM` for fixed sets of values**
5. **Set `NOT NULL` with default values where appropriate**

### 6.4 Frontend Best Practices

1. **Use semantic HTML**
2. **Follow mobile-first responsive design**
3. **Use Tailwind utility classes consistently**
4. **Add alt text to images**
5. **Use proper form labels and ARIA attributes**

---

## 7. Testing Guide

### 7.1 Running Tests

```bash
# Install PHPUnit (if not installed)
composer require --dev phpunit/phpunit

# Run all tests
./vendor/bin/phpunit tests/

# Run specific test file
./vendor/bin/phpunit tests/DatabaseTest.php

# Run specific test method
./vendor/bin/phpunit --filter testSingleton tests/DatabaseTest.php

# Run with coverage
./vendor/bin/phpunit --coverage-html coverage tests/
```

### 7.2 Test Structure

```
tests/
├── DatabaseTest.php      # 12 tests for Database class
├── BukuTamuTest.php      # 12 tests for BukuTamu model
├── KepuasanTest.php      # 12 tests for Kepuasan model
├── AdminTest.php         # 12 tests for Admin model
├── FunctionsTest.php     # 20 tests for helper functions
├── PerformanceTest.php   # 20 performance benchmarks
└── SecurityTest.php      # 25 security compliance tests
```

### 7.3 Writing New Tests

```php
<?php
use PHPUnit\Framework\TestCase;

class MyFeatureTest extends TestCase {
    private $db;

    protected function setUp(): void {
        $this->db = Database::getInstance();
    }

    public function testMyFeature(): void {
        // Arrange
        $input = 'test';
        
        // Act
        $result = myFunction($input);
        
        // Assert
        $this->assertEquals('expected', $result);
    }
}
```

---

## 8. Deployment Checklist

### 8.1 Pre-Deployment

- [ ] Update `config/database.php` with production credentials
- [ ] Change default admin password
- [ ] Disable error reporting in `config/app.php`
- [ ] Set `session.cookie_secure` to `1` (HTTPS)
- [ ] Update `BASE_URL` in `config/app.php`
- [ ] Run all tests: `./vendor/bin/phpunit tests/`
- [ ] Backup existing database (if updating)

### 8.2 Deployment Steps

```bash
# 1. Upload files to server
# Target: public_html/pelita/

# 2. Set permissions
chmod 755 public/assets
chmod 644 config/*.php

# 3. Create database via cPanel
# Import sql/pelita.sql

# 4. Verify configuration
# Check config/database.php

# 5. Test application
# - Public pages
# - Admin login
# - Form submissions
# - Export functionality
```

### 8.3 Post-Deployment

- [ ] Monitor error logs
- [ ] Test all critical functionality
- [ ] Verify database connections
- [ ] Check email notifications (if any)
- [ ] Update documentation

---

## Appendix A: Quick Reference

### A.1 Database Schema Summary

| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `admin` | Admin users | id, username, password, is_active |
| `buku_tamu` | Guest entries | id, nama, nohp, asal, keperluan, nomor_antrian |
| `kepuasan` | Satisfaction surveys | id, email, rating, komentar |
| `log_activity` | Audit log | id, admin_id, action, table_name |
| `ref_bulan` | Month names | id, nama |
| `ref_keperluan` | Visit purposes | id, nama, is_active |
| `ref_pekerjaan` | Occupations | id, nama |
| `ref_pendidikan` | Education levels | id, nama, urutan |

### A.2 API Endpoints Summary

| Method | Endpoint | Auth | Purpose |
|--------|----------|------|---------|
| GET | `/` | No | Landing page |
| POST | `/public/buku-tamu.php` | No | Submit guest form |
| POST | `/public/kepuasan.php` | No | Submit satisfaction |
| POST | `/admin/login.php` | No | Admin login |
| GET | `/admin/` | Yes | Dashboard |
| GET | `/admin/buku-tamu/` | Yes | Guest list |
| GET | `/admin/kepuasan/` | Yes | Satisfaction list |
| GET | `/admin/buku-tamu/export-excel.php` | Yes | Export Excel |
| GET | `/admin/buku-tamu/export-pdf.php` | Yes | Export PDF |

### A.3 Default Credentials

| Role | Username | Password | Note |
|------|----------|----------|------|
| Admin | `admin_pelita` | (hashed) | Change immediately |

### A.4 Color Palette

| Name | Hex | Usage |
|------|-----|-------|
| BPS Blue | `#003D7A` | Primary color |
| BPS Dark | `#002855` | Dark variant |
| SE Coral | `#E85D4C` | Accent 1 |
| SE Orange | `#F47920` | Accent 2 |
| SE Teal | `#00A19B` | Accent 3 |

---

## Appendix B: Contact & Support

| Resource | Contact |
|----------|---------|
| **Documentation** | `docs/TECHNICAL_SPECIFICATION.md` |
| **Tests** | `tests/` directory |
| **Original Docs** | `Dokumentasi-PELITA V1.0.0.md` |
| **Email** | bps3509@bps.go.id |
| **Phone** | (0331) 487642 |
| **Website** | https://jemberkab.bps.go.id |

---

**End of Knowledge Transfer Document**

*This document provides comprehensive understanding of the PELITA system. For detailed technical specifications, refer to `docs/TECHNICAL_SPECIFICATION.md`.*
