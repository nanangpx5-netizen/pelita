<?php
/**
 * PELITA - Admin Model Unit Tests
 * @version 1.0.0
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/Admin.php';

use PHPUnit\Framework\TestCase;

class AdminTest extends TestCase {
    private Admin $admin;
    private Database $db;
    private int $testAdminId;

    protected function setUp(): void {
        $this->admin = new Admin();
        $this->db = Database::getInstance();
        
        // Create test admin
        $this->testAdminId = $this->db->insert('admin', [
            'username' => 'test_admin_' . uniqid(),
            'password' => password_hash('test_password', PASSWORD_DEFAULT),
            'nama' => 'Test Admin',
            'email' => 'test@example.com',
            'is_active' => 1
        ]);
    }

    protected function tearDown(): void {
        // Cleanup test admin
        if (isset($this->testAdminId)) {
            $this->db->delete('admin', "id = :id", ['id' => $this->testAdminId]);
        }
    }

    /**
     * Test 1: Find Admin by Username
     * Verify that findByUsername() returns correct admin data
     */
    public function testFindByUsername(): void {
        // Get the test admin username
        $testAdmin = $this->db->fetch("SELECT * FROM admin WHERE id = :id", ['id' => $this->testAdminId]);
        
        $result = $this->admin->findByUsername($testAdmin['username']);
        
        $this->assertIsArray($result, 'Should return array');
        $this->assertEquals($this->testAdminId, $result['id'], 'ID should match');
        $this->assertEquals($testAdmin['username'], $result['username'], 'Username should match');
        $this->assertEquals($testAdmin['nama'], $result['nama'], 'Name should match');
        $this->assertEquals($testAdmin['email'], $result['email'], 'Email should match');
    }

    /**
     * Test 2: Find Non-Existing Username
     * Verify that findByUsername() returns null for non-existing username
     */
    public function testFindByUsernameNotFound(): void {
        $result = $this->admin->findByUsername('non_existing_user_xyz');
        $this->assertNull($result, 'Should return null for non-existing username');
    }

    /**
     * Test 3: Find Inactive Admin
     * Verify that findByUsername() doesn't return inactive admins
     */
    public function testFindByUsernameInactive(): void {
        // Create inactive admin
        $inactiveId = $this->db->insert('admin', [
            'username' => 'inactive_admin_' . uniqid(),
            'password' => password_hash('test_password', PASSWORD_DEFAULT),
            'nama' => 'Inactive Admin',
            'email' => 'inactive@example.com',
            'is_active' => 0
        ]);

        $result = $this->admin->findByUsername('inactive_admin_' . substr($inactiveId, -5));
        $this->assertNull($result, 'Should return null for inactive admin');

        // Cleanup
        $this->db->delete('admin', "id = :id", ['id' => $inactiveId]);
    }

    /**
     * Test 4: Find Admin by ID
     * Verify that findById() returns correct admin data
     */
    public function testFindById(): void {
        $result = $this->admin->findById($this->testAdminId);
        
        $this->assertIsArray($result, 'Should return array');
        $this->assertEquals($this->testAdminId, $result['id'], 'ID should match');
        $this->assertArrayHasKey('username', $result, 'Should have username key');
        $this->assertArrayHasKey('nama', $result, 'Should have nama key');
        $this->assertArrayHasKey('email', $result, 'Should have email key');
    }

    /**
     * Test 5: Find Non-Existing ID
     * Verify that findById() returns null for non-existing ID
     */
    public function testFindByIdNotFound(): void {
        $result = $this->admin->findById(999999);
        $this->assertNull($result, 'Should return null for non-existing ID');
    }

    /**
     * Test 6: Update Last Login
     * Verify that updateLastLogin() updates the last_login timestamp
     */
    public function testUpdateLastLogin(): void {
        // Get initial last_login
        $before = $this->admin->findById($this->testAdminId);
        $initialLastLogin = $before['last_login'];
        
        // Wait a bit to ensure timestamp difference
        sleep(1);
        
        // Update last login
        $this->admin->updateLastLogin($this->testAdminId);
        
        // Get updated last_login
        $after = $this->admin->findById($this->testAdminId);
        $updatedLastLogin = $after['last_login'];
        
        $this->assertNotNull($updatedLastLogin, 'last_login should be set');
        
        // If initial was null, just check it's now set
        if ($initialLastLogin === null) {
            $this->assertNotNull($updatedLastLogin, 'last_login should be updated from null');
        } else {
            $this->assertNotEquals($initialLastLogin, $updatedLastLogin, 'last_login should be updated');
        }
    }

    /**
     * Test 7: Change Password
     * Verify that changePassword() updates password hash
     */
    public function testChangePassword(): void {
        $newPassword = 'new_password_123';
        
        $result = $this->admin->changePassword($this->testAdminId, $newPassword);
        $this->assertTrue($result, 'changePassword() should return true');
        
        // Verify password was changed
        $admin = $this->admin->findById($this->testAdminId);
        $this->assertTrue(
            password_verify($newPassword, $admin['password']),
            'New password should be verified'
        );
    }

    /**
     * Test 8: Verify Password
     * Verify that verifyPassword() correctly validates passwords
     */
    public function testVerifyPassword(): void {
        $correctPassword = 'test_password';
        $wrongPassword = 'wrong_password';
        
        // Get the test admin
        $testAdmin = $this->db->fetch("SELECT * FROM admin WHERE id = :id", ['id' => $this->testAdminId]);
        
        // Test correct password
        $result = $this->admin->verifyPassword($correctPassword, $testAdmin['password']);
        $this->assertTrue($result, 'Should verify correct password');
        
        // Test wrong password
        $result = $this->admin->verifyPassword($wrongPassword, $testAdmin['password']);
        $this->assertFalse($result, 'Should reject wrong password');
    }

    /**
     * Test 9: Change Password with Non-Existing ID
     * Verify that changePassword() returns false for non-existing ID
     */
    public function testChangePasswordNonExisting(): void {
        $result = $this->admin->changePassword(999999, 'new_password');
        $this->assertFalse($result, 'Should return false for non-existing ID');
    }

    /**
     * Test 10: Password Hash Strength
     * Verify that passwords are hashed with bcrypt
     */
    public function testPasswordHashStrength(): void {
        $admin = $this->admin->findById($this->testAdminId);
        $hash = $admin['password'];
        
        // Bcrypt hashes start with $2y$ (or $2a$, $2b$)
        $this->assertMatchesRegularExpression('/^\$2[aby]\$/', $hash, 'Password should be hashed with bcrypt');
        
        // Hash should be 60 characters long
        $this->assertEquals(60, strlen($hash), 'Bcrypt hash should be 60 characters');
    }

    /**
     * Test 11: Unique Username Constraint
     * Verify that duplicate usernames are not allowed
     */
    public function testUniqueUsername(): void {
        $testAdmin = $this->db->fetch("SELECT * FROM admin WHERE id = :id", ['id' => $this->testAdminId]);
        
        // Try to create admin with same username
        try {
            $this->db->insert('admin', [
                'username' => $testAdmin['username'],
                'password' => password_hash('test', PASSWORD_DEFAULT),
                'nama' => 'Duplicate Admin',
                'email' => 'duplicate@example.com',
                'is_active' => 1
            ]);
            $this->fail('Should throw exception for duplicate username');
        } catch (Exception $e) {
            $this->assertStringContainsString('Duplicate', $e->getMessage(), 'Should throw duplicate entry error');
        }
    }

    /**
     * Test 12: Admin Fields Structure
     * Verify that admin record has all expected fields
     */
    public function testAdminFieldsStructure(): void {
        $admin = $this->admin->findById($this->testAdminId);
        
        $expectedFields = ['id', 'username', 'password', 'nama', 'email', 'last_login', 'is_active', 'created_at', 'updated_at'];
        
        foreach ($expectedFields as $field) {
            $this->assertArrayHasKey($field, $admin, "Should have field: {$field}");
        }
    }
}
