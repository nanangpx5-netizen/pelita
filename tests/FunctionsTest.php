<?php
/**
 * PELITA - Helper Functions Unit Tests
 * @version 1.0.0
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/csrf.php';

use PHPUnit\Framework\TestCase;

class FunctionsTest extends TestCase {
    private Database $db;

    protected function setUp(): void {
        $this->db = Database::getInstance();
    }

    /**
     * Test 1: base_url()
     * Verify that base_url() generates correct URLs
     */
    public function testBaseUrl(): void {
        $result = base_url('admin/login.php');
        $this->assertStringEndsWith('/admin/login.php', $result, 'Should append path to base URL');
        
        $result = base_url('');
        $this->assertEquals(BASE_URL, $result, 'Should return base URL when path is empty');
        
        $result = base_url('/');
        $this->assertEquals(BASE_URL . '/', $result, 'Should handle leading slash');
    }

    /**
     * Test 2: asset_url()
     * Verify that asset_url() generates correct asset URLs
     */
    public function testAssetUrl(): void {
        $result = asset_url('css/style.css');
        $this->assertStringContainsString('/public/assets/css/style.css', $result, 'Should generate asset URL');
        
        $result = asset_url('/js/script.js');
        $this->assertStringContainsString('/public/assets/js/script.js', $result, 'Should handle leading slash');
    }

    /**
     * Test 3: sanitize()
     * Verify that sanitize() prevents XSS attacks
     */
    public function testSanitize(): void {
        $input = '<script>alert("xss")</script>';
        $output = sanitize($input);
        
        $this->assertStringNotContainsString('<script>', $output, 'Should remove script tags');
        $this->assertStringContainsString('<script>', $output, 'Should escape HTML entities');
    }

    /**
     * Test 4: sanitize() with Trim
     * Verify that sanitize() trims whitespace
     */
    public function testSanitizeTrim(): void {
        $input = '  test  ';
        $output = sanitize($input);
        $this->assertEquals('test', $output, 'Should trim whitespace');
    }

    /**
     * Test 5: sanitize_array()
     * Verify that sanitize_array() sanitizes all string values
     */
    public function testSanitizeArray(): void {
        $input = [
            'name' => '<script>alert("xss")</script>',
            'email' => 'test@example.com',
            'age' => 25,
            'nested' => [
                'value' => '<b>bold</b>'
            ]
        ];
        
        $output = sanitize_array($input);
        
        $this->assertStringNotContainsString('<script>', $output['name'], 'Should sanitize string values');
        $this->assertEquals('test@example.com', $output['email'], 'Should keep valid email');
        $this->assertEquals(25, $output['age'], 'Should keep numeric values');
        $this->assertStringNotContainsString('<b>', $output['nested']['value'], 'Should sanitize nested values');
    }

    /**
     * Test 6: validate_email()
     * Verify that validate_email() correctly validates email addresses
     */
    public function testValidateEmail(): void {
        // Valid emails
        $this->assertTrue(validate_email('test@example.com'), 'Should accept valid email');
        $this->assertTrue(validate_email('user.name+tag@domain.co.id'), 'Should accept complex valid email');
        
        // Invalid emails
        $this->assertFalse(validate_email('invalid'), 'Should reject invalid email');
        $this->assertFalse(validate_email('test@'), 'Should reject incomplete email');
        $this->assertFalse(validate_email('@example.com'), 'Should reject email without local part');
        $this->assertFalse(validate_email('test example.com'), 'Should reject email without @');
    }

    /**
     * Test 7: validate_phone()
     * Verify that validate_phone() correctly validates Indonesian phone numbers
     */
    public function testValidatePhone(): void {
        // Valid Indonesian phone numbers
        $this->assertTrue(validate_phone('08123456789'), 'Should accept 08 prefix');
        $this->assertTrue(validate_phone('628123456789'), 'Should accept 62 prefix');
        $this->assertTrue(validate_phone('+628123456789'), 'Should accept +62 prefix');
        $this->assertTrue(validate_phone('0812 3456 789'), 'Should accept phone with spaces');
        
        // Invalid phone numbers
        $this->assertFalse(validate_phone('123'), 'Should reject too short');
        $this->assertFalse(validate_phone('08123456789012345'), 'Should reject too long');
        $this->assertFalse(validate_phone('123456789'), 'Should reject without valid prefix');
        $this->assertFalse(validate_phone('abc1234567'), 'Should reject non-numeric');
    }

    /**
     * Test 8: format_tanggal()
     * Verify that format_tanggal() formats dates in Indonesian
     */
    public function testFormatTanggal(): void {
        $result = format_tanggal('2026-01-15', 'd F Y');
        $this->assertEquals('15 Januari 2026', $result, 'Should format date in Indonesian');
        
        $result = format_tanggal('2026-02-28', 'l, d F Y');
        $this->assertStringContainsString('Februari', $result, 'Should contain Indonesian month name');
        
        $result = format_tanggal('2026-12-25', 'd/m/Y');
        $this->assertEquals('25/12/2026', $result, 'Should format with custom format');
    }

    /**
     * Test 9: format_waktu()
     * Verify that format_waktu() formats datetime correctly
     */
    public function testFormatWaktu(): void {
        $result = format_waktu('2026-01-15 14:30:45');
        $this->assertEquals('15/01/2026 14:30', $result, 'Should format datetime');
    }

    /**
     * Test 10: get_nama_bulan()
     * Verify that get_nama_bulan() returns correct Indonesian month names
     */
    public function testGetNamaBulan(): void {
        $this->assertEquals('Januari', get_nama_bulan(1), 'Should return Januari');
        $this->assertEquals('Februari', get_nama_bulan(2), 'Should return Februari');
        $this->assertEquals('Maret', get_nama_bulan(3), 'Should return Maret');
        $this->assertEquals('April', get_nama_bulan(4), 'Should return April');
        $this->assertEquals('Mei', get_nama_bulan(5), 'Should return Mei');
        $this->assertEquals('Juni', get_nama_bulan(6), 'Should return Juni');
        $this->assertEquals('Juli', get_nama_bulan(7), 'Should return Juli');
        $this->assertEquals('Agustus', get_nama_bulan(8), 'Should return Agustus');
        $this->assertEquals('September', get_nama_bulan(9), 'Should return September');
        $this->assertEquals('Oktober', get_nama_bulan(10), 'Should return Oktober');
        $this->assertEquals('November', get_nama_bulan(11), 'Should return November');
        $this->assertEquals('Desember', get_nama_bulan(12), 'Should return Desember');
    }

    /**
     * Test 11: flash() - Set and Get
     * Verify that flash() stores and retrieves flash messages
     */
    public function testFlash(): void {
        // Start session if not started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Set flash message
        flash('success', 'Operation successful!');
        
        // Get flash message
        $message = flash('success');
        
        $this->assertEquals('Operation successful!', $message, 'Should retrieve flash message');
        
        // Verify flash message is removed after retrieval
        $message = flash('success');
        $this->assertNull($message, 'Flash message should be removed after retrieval');
    }

    /**
     * Test 12: has_flash()
     * Verify that has_flash() checks for flash message existence
     */
    public function testHasFlash(): void {
        // Start session if not started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Initially no flash message
        $this->assertFalse(has_flash('test_key'), 'Should return false for non-existing flash');
        
        // Set flash message
        flash('test_key', 'Test message');
        
        // Now flash message exists
        $this->assertTrue(has_flash('test_key'), 'Should return true for existing flash');
        
        // Clear it
        flash('test_key');
    }

    /**
     * Test 13: paginate()
     * Verify that paginate() generates correct pagination data
     */
    public function testPaginate(): void {
        $result = paginate(100, 3, 20, '/admin/buku-tamu/');
        
        $this->assertIsArray($result, 'Should return array');
        $this->assertEquals(100, $result['total'], 'Total should match');
        $this->assertEquals(20, $result['per_page'], 'Per page should match');
        $this->assertEquals(3, $result['current_page'], 'Current page should match');
        $this->assertEquals(5, $result['total_pages'], 'Total pages should be calculated correctly');
        $this->assertTrue($result['has_prev'], 'Should have previous page');
        $this->assertTrue($result['has_next'], 'Should have next page');
        $this->assertEquals('/admin/buku-tamu/?page=2', $result['prev_url'], 'Previous URL should be correct');
        $this->assertEquals('/admin/buku-tamu/?page=4', $result['next_url'], 'Next URL should be correct');
    }

    /**
     * Test 14: paginate() - First Page
     * Verify pagination on first page
     */
    public function testPaginateFirstPage(): void {
        $result = paginate(100, 1, 20, '/admin/buku-tamu/');
        
        $this->assertFalse($result['has_prev'], 'Should not have previous page on first page');
        $this->assertNull($result['prev_url'], 'Previous URL should be null on first page');
        $this->assertTrue($result['has_next'], 'Should have next page');
    }

    /**
     * Test 15: paginate() - Last Page
     * Verify pagination on last page
     */
    public function testPaginateLastPage(): void {
        $result = paginate(100, 5, 20, '/admin/buku-tamu/');
        
        $this->assertTrue($result['has_prev'], 'Should have previous page on last page');
        $this->assertFalse($result['has_next'], 'Should not have next page on last page');
        $this->assertNull($result['next_url'], 'Next URL should be null on last page');
    }

    /**
     * Test 16: get_client_ip()
     * Verify that get_client_ip() returns IP address
     */
    public function testGetClientIp(): void {
        $ip = get_client_ip();
        
        $this->assertIsString($ip, 'Should return string');
        $this->assertMatchesRegularExpression('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', $ip, 'Should return valid IP address format');
    }

    /**
     * Test 17: get_ref_data()
     * Verify that get_ref_data() returns reference table data
     */
    public function testGetRefData(): void {
        $data = get_ref_data('ref_bulan');
        
        $this->assertIsArray($data, 'Should return array');
        $this->assertCount(12, $data, 'Should return 12 months');
        $this->assertEquals('Januari', $data[0]['nama'], 'First month should be Januari');
    }

    /**
     * Test 18: csrf_token()
     * Verify that csrf_token() generates and returns CSRF token
     */
    public function testCsrfToken(): void {
        // Start session if not started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $token1 = csrf_token();
        $token2 = csrf_token();
        
        $this->assertIsString($token1, 'Should return string');
        $this->assertEquals(64, strlen($token1), 'Token should be 64 characters (32 bytes hex)');
        $this->assertEquals($token1, $token2, 'Should return same token on subsequent calls');
    }

    /**
     * Test 19: verify_csrf()
     * Verify that verify_csrf() validates CSRF tokens
     */
    public function testVerifyCsrf(): void {
        // Start session if not started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $token = csrf_token();
        
        $this->assertTrue(verify_csrf($token), 'Should verify correct token');
        $this->assertFalse(verify_csrf('invalid_token'), 'Should reject invalid token');
    }

    /**
     * Test 20: csrf_field()
     * Verify that csrf_field() generates HTML hidden input
     */
    public function testCsrfField(): void {
        // Start session if not started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $field = csrf_field();
        
        $this->assertStringContainsString('<input type="hidden"', $field, 'Should generate hidden input');
        $this->assertStringContainsString('name="csrf_token"', $field, 'Should have csrf_token name');
        $this->assertStringContainsString('value="', $field, 'Should have value attribute');
    }
}
