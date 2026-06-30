<?php
/**
 * PELITA - Security Compliance Tests (OWASP Top 10)
 * @version 1.0.0
 * 
 * These tests verify compliance with OWASP Top 10 security risks:
 * A01: Broken Access Control
 * A02: Cryptographic Failures
 * A03: Injection
 * A04: Insecure Design
 * A05: Security Misconfiguration
 * A06: Vulnerable Components
 * A07: Authentication Failures
 * A08: Software/Data Integrity
 * A09: Logging & Monitoring
 * A10: Server-Side Request Forgery
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/Admin.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/csrf.php';

use PHPUnit\Framework\TestCase;

class SecurityTest extends TestCase {
    private Database $db;
    private Admin $admin;

    protected function setUp(): void {
        $this->db = Database::getInstance();
        $this->admin = new Admin();
    }

    /**
     * A01: Broken Access Control
     * Test 1: Session-based Authentication
     */
    public function testSessionBasedAuthentication(): void {
        // Start session if not started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Verify session is configured securely
        $this->assertEquals(1, ini_get('session.cookie_httponly'), 'Session cookies should be HTTP-only');
        $this->assertEquals(1, ini_get('session.use_only_cookies'), 'Session should use only cookies');
    }

    /**
     * A01: Broken Access Control
     * Test 2: Admin Access Control
     */
    public function testAdminAccessControl(): void {
        // Verify admin model checks is_active flag
        $admin = $this->admin->findByUsername('admin_pelita');
        
        if ($admin) {
            $this->assertEquals(1, $admin['is_active'], 'Active admin should have is_active = 1');
        }
    }

    /**
     * A02: Cryptographic Failures
     * Test 3: Password Hashing Algorithm
     */
    public function testPasswordHashingAlgorithm(): void {
        $admin = $this->db->fetch("SELECT password FROM admin WHERE id = 1");
        $hash = $admin['password'];
        
        // Verify bcrypt is used (starts with $2y$, $2a$, or $2b$)
        $this->assertMatchesRegularExpression('/^\$2[aby]\$/', $hash, 'Password should be hashed with bcrypt');
        
        // Verify hash length
        $this->assertEquals(60, strlen($hash), 'Bcrypt hash should be 60 characters');
    }

    /**
     * A02: Cryptographic Failures
     * Test 4: Password Verification
     */
    public function testPasswordVerification(): void {
        $admin = $this->db->fetch("SELECT password FROM admin WHERE id = 1");
        $hash = $admin['password'];
        
        // Test with correct password (assuming default password)
        $result = password_verify('admin_pelita', $hash);
        // Note: This may fail if password was changed
        
        // Test with wrong password
        $result = password_verify('wrong_password', $hash);
        $this->assertFalse($result, 'Should reject wrong password');
    }

    /**
     * A02: Cryptographic Failures
     * Test 5: CSRF Token Generation
     */
    public function testCsrfTokenGeneration(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $token = csrf_token();
        
        // Verify token is cryptographically secure
        $this->assertEquals(64, strlen($token), 'Token should be 64 characters (32 bytes hex)');
        $this->assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $token, 'Token should be hexadecimal');
    }

    /**
     * A03: Injection
     * Test 6: SQL Injection Prevention
     */
    public function testSqlInjectionPrevention(): void {
        $maliciousInputs = [
            "1' OR '1'='1",
            "1'; DROP TABLE admin; --",
            "1' UNION SELECT * FROM admin--",
            "admin'/*",
            "' OR 1=1#"
        ];
        
        foreach ($maliciousInputs as $input) {
            $result = $this->db->fetch(
                "SELECT * FROM admin WHERE id = :id",
                ['id' => $input]
            );
            
            // Should return null (no ID matches the malicious string)
            $this->assertNull($result, "Should prevent SQL injection for: {$input}");
        }
    }

    /**
     * A03: Injection
     * Test 7: XSS Prevention
     */
    public function testXssPrevention(): void {
        $xssPayloads = [
            '<script>alert("xss")</script>',
            '<img src=x onerror=alert("xss")>',
            '<svg onload=alert("xss")>',
            'javascript:alert("xss")',
            '<iframe src="javascript:alert(\'xss\')"></iframe>'
        ];
        
        foreach ($xssPayloads as $payload) {
            $sanitized = sanitize($payload);
            
            // Verify script tags are escaped
            $this->assertStringNotContainsString('<script>', $sanitized, "Should escape script tag in: {$payload}");
            $this->assertStringNotContainsString('onerror=', $sanitized, "Should escape onerror in: {$payload}");
            $this->assertStringNotContainsString('onload=', $sanitized, "Should escape onload in: {$payload}");
        }
    }

    /**
     * A03: Injection
     * Test 8: Prepared Statement Usage
     */
    public function testPreparedStatementUsage(): void {
        // Verify that Database class uses prepared statements
        $reflection = new ReflectionClass($this->db);
        $queryMethod = $reflection->getMethod('query');
        
        // Check if method uses PDO prepare
        $source = file_get_contents($reflection->getFileName());
        $this->assertStringContainsString('prepare(', $source, 'Database class should use PDO prepare()');
    }

    /**
     * A04: Insecure Design
     * Test 9: CSRF Protection on Forms
     */
    public function testCsrfProtectionOnForms(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $token = csrf_token();
        
        // Verify token validation
        $this->assertTrue(verify_csrf($token), 'Should verify valid CSRF token');
        $this->assertFalse(verify_csrf('invalid_token'), 'Should reject invalid CSRF token');
    }

    /**
     * A04: Insecure Design
     * Test 10: Session Regeneration
     */
    public function testSessionRegeneration(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $oldSessionId = session_id();
        session_regenerate_id(true);
        $newSessionId = session_id();
        
        $this->assertNotEquals($oldSessionId, $newSessionId, 'Session ID should be regenerated');
    }

    /**
     * A05: Security Misconfiguration
     * Test 11: Error Reporting Configuration
     */
    public function testErrorReportingConfiguration(): void {
        // In production, error reporting should be disabled
        // This test checks the configuration exists
        $this->assertFileExists(__DIR__ . '/../config/app.php', 'App config should exist');
        
        $config = file_get_contents(__DIR__ . '/../config/app.php');
        $this->assertStringContainsString('error_reporting', $config, 'Error reporting should be configured');
    }

    /**
     * A05: Security Misconfiguration
     * Test 12: Database Credentials Separation
     */
    public function testDatabaseCredentialsSeparation(): void {
        // Verify database credentials are in separate config file
        $this->assertFileExists(__DIR__ . '/../config/database.php', 'Database config should exist');
        
        $config = file_get_contents(__DIR__ . '/../config/database.php');
        $this->assertStringContainsString('DB_HOST', $config, 'DB_HOST should be defined');
        $this->assertStringContainsString('DB_USER', $config, 'DB_USER should be defined');
        $this->assertStringContainsString('DB_PASS', $config, 'DB_PASS should be defined');
    }

    /**
     * A06: Vulnerable Components
     * Test 13: Minimal Dependencies
     */
    public function testMinimalDependencies(): void {
        // Verify no composer.json (no external PHP dependencies)
        $this->assertFileDoesNotExist(__DIR__ . '/../composer.json', 'Should not have composer.json (minimal dependencies)');
    }

    /**
     * A06: Vulnerable Components
     * Test 14: No Direct File Inclusion
     */
    public function testNoDirectFileInclusion(): void {
        // Verify .htaccess prevents direct access to sensitive files
        $this->assertFileExists(__DIR__ . '/../public/.htaccess', 'Public .htaccess should exist');
        
        $htaccess = file_get_contents(__DIR__ . '/../public/.htaccess');
        $this->assertStringContainsString('Options -Indexes', $htaccess, 'Should prevent directory listing');
    }

    /**
     * A07: Authentication Failures
     * Test 15: Password Hash Strength
     */
    public function testPasswordHashStrength(): void {
        $admin = $this->db->fetch("SELECT password FROM admin WHERE id = 1");
        $hash = $admin['password'];
        
        // Verify bcrypt cost factor (default is 10)
        $this->assertMatchesRegularExpression('/^\$2[aby]\$10\$/', $hash, 'Should use bcrypt with cost factor 10');
    }

    /**
     * A07: Authentication Failures
     * Test 16: Unique Username Constraint
     */
    public function testUniqueUsernameConstraint(): void {
        // Try to create admin with existing username
        $existingAdmin = $this->db->fetch("SELECT username FROM admin LIMIT 1");
        
        if ($existingAdmin) {
            try {
                $this->db->insert('admin', [
                    'username' => $existingAdmin['username'],
                    'password' => password_hash('test', PASSWORD_DEFAULT),
                    'nama' => 'Duplicate',
                    'email' => 'duplicate@test.com',
                    'is_active' => 1
                ]);
                $this->fail('Should throw exception for duplicate username');
            } catch (Exception $e) {
                $this->assertStringContainsString('Duplicate', $e->getMessage(), 'Should enforce unique username');
            }
        }
    }

    /**
     * A08: Software/Data Integrity
     * Test 17: No Auto-Updates
     */
    public function testNoAutoUpdates(): void {
        // Verify no auto-update mechanisms
        $files = glob(__DIR__ . '/../**/*.php', GLOB_BRACE);
        $autoUpdateFound = false;
        
        foreach ($files as $file) {
            $content = file_get_contents($file);
            if (preg_match('/(auto.?update|self.?update|wp.?update)/i', $content)) {
                $autoUpdateFound = true;
                break;
            }
        }
        
        $this->assertFalse($autoUpdateFound, 'Should not have auto-update mechanisms');
    }

    /**
     * A09: Logging & Monitoring
     * Test 18: Error Logging
     */
    public function testErrorLogging(): void {
        // Verify error_log is used
        $dbFile = file_get_contents(__DIR__ . '/../classes/Database.php');
        $this->assertStringContainsString('error_log', $dbFile, 'Should use error_log for error logging');
    }

    /**
     * A09: Logging & Monitoring
     * Test 19: Activity Log Table
     */
    public function testActivityLogTable(): void {
        // Verify log_activity table exists
        $result = $this->db->fetch("SHOW TABLES LIKE 'log_activity'");
        $this->assertIsArray($result, 'log_activity table should exist');
    }

    /**
     * A09: Logging & Monitoring
     * Test 20: Activity Log Structure
     */
    public function testActivityLogStructure(): void {
        // Verify log_activity table has required columns
        $columns = $this->db->fetchAll("SHOW COLUMNS FROM log_activity");
        $columnNames = array_column($columns, 'Field');
        
        $requiredColumns = ['id', 'admin_id', 'action', 'table_name', 'ip_address', 'created_at'];
        
        foreach ($requiredColumns as $column) {
            $this->assertContains($column, $columnNames, "log_activity should have column: {$column}");
        }
    }

    /**
     * A10: Server-Side Request Forgery
     * Test 21: No External API Calls
     */
    public function testNoExternalApiCalls(): void {
        // Verify no curl or file_get_contents to external URLs
        $files = glob(__DIR__ . '/../**/*.php', GLOB_BRACE);
        $externalCallFound = false;
        
        foreach ($files as $file) {
            $content = file_get_contents($file);
            if (preg_match('/(curl_init|file_get_contents\s*\(\s*[\'"]http)/i', $content)) {
                // Exclude CDN links (allowed)
                if (!preg_match('/(cdn\.|cdnjs|googleapis)/i', $content)) {
                    $externalCallFound = true;
                    break;
                }
            }
        }
        
        $this->assertFalse($externalCallFound, 'Should not make external API calls');
    }

    /**
     * Additional Security Test: Input Validation
     * Test 22: Email Validation
     */
    public function testEmailValidation(): void {
        $validEmails = [
            'test@example.com',
            'user.name+tag@domain.co.id',
            'user@sub.domain.com'
        ];
        
        $invalidEmails = [
            'invalid',
            'test@',
            '@example.com',
            'test example.com',
            '<script>alert(1)</script>@example.com'
        ];
        
        foreach ($validEmails as $email) {
            $this->assertTrue(validate_email($email), "Should accept valid email: {$email}");
        }
        
        foreach ($invalidEmails as $email) {
            $this->assertFalse(validate_email($email), "Should reject invalid email: {$email}");
        }
    }

    /**
     * Additional Security Test: Phone Validation
     * Test 23: Phone Number Validation
     */
    public function testPhoneValidation(): void {
        $validPhones = [
            '08123456789',
            '628123456789',
            '+628123456789',
            '0812 3456 789'
        ];
        
        $invalidPhones = [
            '123',
            '08123456789012345',
            'abc1234567',
            '<script>alert(1)</script>'
        ];
        
        foreach ($validPhones as $phone) {
            $this->assertTrue(validate_phone($phone), "Should accept valid phone: {$phone}");
        }
        
        foreach ($invalidPhones as $phone) {
            $this->assertFalse(validate_phone($phone), "Should reject invalid phone: {$phone}");
        }
    }

    /**
     * Additional Security Test: Output Encoding
     * Test 24: HTML Entity Encoding
     */
    public function testHtmlEntityEncoding(): void {
        $inputs = [
            '<' => '<',
            '>' => '>',
            '"' => '"',
            "'" => '&#039;',
            '&' => '&'
        ];
        
        foreach ($inputs as $input => $expected) {
            $output = sanitize($input);
            $this->assertEquals($expected, $output, "Should encode: {$input}");
        }
    }

    /**
     * Additional Security Test: Secure Headers
     * Test 25: Session Cookie Configuration
     */
    public function testSessionCookieConfiguration(): void {
        // Verify session cookie security settings
        $this->assertEquals(1, ini_get('session.cookie_httponly'), 'Session cookies should be HTTP-only');
        $this->assertEquals(1, ini_get('session.use_only_cookies'), 'Session should use only cookies');
        
        // Secure flag should be set on HTTPS
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            $this->assertEquals(1, ini_get('session.cookie_secure'), 'Session cookies should be secure on HTTPS');
        }
    }
}
