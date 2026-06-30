<?php
/**
 * PELITA - Database Class Unit Tests
 * @version 1.0.0
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Database.php';

use PHPUnit\Framework\TestCase;

class DatabaseTest extends TestCase {
    private Database $db;

    protected function setUp(): void {
        $this->db = Database::getInstance();
    }

    /**
     * Test 1: Singleton Pattern
     * Verify that Database::getInstance() returns the same instance
     */
    public function testSingleton(): void {
        $db1 = Database::getInstance();
        $db2 = Database::getInstance();
        $this->assertSame($db1, $db2, 'Database should implement Singleton pattern');
    }

    /**
     * Test 2: Database Connection
     * Verify that database connection is established
     */
    public function testConnection(): void {
        $result = $this->db->fetch("SELECT 1 as test");
        $this->assertIsArray($result, 'Query should return an array');
        $this->assertEquals(1, $result['test'], 'Database should return test value');
    }

    /**
     * Test 3: Query Execution
     * Verify that queries execute correctly with parameters
     */
    public function testQueryExecution(): void {
        $stmt = $this->db->query("SELECT :num as number", ['num' => 42]);
        $result = $stmt->fetch();
        $this->assertEquals(42, $result['number'], 'Parameter binding should work correctly');
    }

    /**
     * Test 4: Fetch Single Record
     * Verify fetch() method returns single record or null
     */
    public function testFetchSingle(): void {
        // Test with existing data
        $result = $this->db->fetch("SELECT * FROM admin LIMIT 1");
        $this->assertIsArray($result, 'Should return array for existing record');
        $this->assertArrayHasKey('id', $result, 'Result should have id key');

        // Test with non-existing data
        $result = $this->db->fetch("SELECT * FROM admin WHERE id = 999999");
        $this->assertNull($result, 'Should return null for non-existing record');
    }

    /**
     * Test 5: Fetch All Records
     * Verify fetchAll() method returns array of records
     */
    public function testFetchAll(): void {
        $results = $this->db->fetchAll("SELECT * FROM ref_bulan");
        $this->assertIsArray($results, 'Should return array');
        $this->assertCount(12, $results, 'Should return 12 months');
    }

    /**
     * Test 6: Insert Operation
     * Verify insert() method creates new record and returns ID
     */
    public function testInsert(): void {
        $testTable = 'log_activity';
        $data = [
            'admin_id' => 1,
            'action' => 'TEST',
            'table_name' => 'test_table',
            'ip_address' => '127.0.0.1'
        ];
        
        $id = $this->db->insert($testTable, $data);
        $this->assertIsInt($id, 'Insert should return integer ID');
        $this->assertGreaterThan(0, $id, 'Insert should return positive ID');

        // Cleanup
        $this->db->delete($testTable, "id = :id", ['id' => $id]);
    }

    /**
     * Test 7: Update Operation
     * Verify update() method modifies existing records
     */
    public function testUpdate(): void {
        // Insert test record
        $id = $this->db->insert('log_activity', [
            'admin_id' => 1,
            'action' => 'TEST_UPDATE',
            'table_name' => 'test_table',
            'ip_address' => '127.0.0.1'
        ]);

        // Update the record
        $affected = $this->db->update(
            'log_activity',
            ['action' => 'TEST_UPDATED'],
            "id = :id",
            ['id' => $id]
        );

        $this->assertEquals(1, $affected, 'Update should affect 1 row');

        // Verify update
        $record = $this->db->fetch("SELECT * FROM log_activity WHERE id = :id", ['id' => $id]);
        $this->assertEquals('TEST_UPDATED', $record['action'], 'Action should be updated');

        // Cleanup
        $this->db->delete('log_activity', "id = :id", ['id' => $id]);
    }

    /**
     * Test 8: Delete Operation
     * Verify delete() method removes records
     */
    public function testDelete(): void {
        // Insert test record
        $id = $this->db->insert('log_activity', [
            'admin_id' => 1,
            'action' => 'TEST_DELETE',
            'table_name' => 'test_table',
            'ip_address' => '127.0.0.1'
        ]);

        // Delete the record
        $affected = $this->db->delete('log_activity', "id = :id", ['id' => $id]);
        $this->assertEquals(1, $affected, 'Delete should affect 1 row');

        // Verify deletion
        $record = $this->db->fetch("SELECT * FROM log_activity WHERE id = :id", ['id' => $id]);
        $this->assertNull($record, 'Record should be deleted');
    }

    /**
     * Test 9: Count Operation
     * Verify count() method returns correct record count
     */
    public function testCount(): void {
        $count = $this->db->count('ref_bulan');
        $this->assertEquals(12, $count, 'Should count 12 months');

        $count = $this->db->count('ref_bulan', "id = :id", ['id' => 1]);
        $this->assertEquals(1, $count, 'Should count 1 record with filter');
    }

    /**
     * Test 10: Transaction Operations
     * Verify beginTransaction(), commit(), and rollBack() work correctly
     */
    public function testTransaction(): void {
        // Start transaction
        $this->assertTrue($this->db->beginTransaction(), 'Should begin transaction');

        // Insert record within transaction
        $id = $this->db->insert('log_activity', [
            'admin_id' => 1,
            'action' => 'TEST_TRANSACTION',
            'table_name' => 'test_table',
            'ip_address' => '127.0.0.1'
        ]);

        // Rollback transaction
        $this->assertTrue($this->db->rollBack(), 'Should rollback transaction');

        // Verify record was not committed
        $record = $this->db->fetch("SELECT * FROM log_activity WHERE id = :id", ['id' => $id]);
        $this->assertNull($record, 'Record should not exist after rollback');

        // Test commit
        $this->db->beginTransaction();
        $id = $this->db->insert('log_activity', [
            'admin_id' => 1,
            'action' => 'TEST_COMMIT',
            'table_name' => 'test_table',
            'ip_address' => '127.0.0.1'
        ]);
        $this->db->commit();

        // Verify record was committed
        $record = $this->db->fetch("SELECT * FROM log_activity WHERE id = :id", ['id' => $id]);
        $this->assertIsArray($record, 'Record should exist after commit');

        // Cleanup
        $this->db->delete('log_activity', "id = :id", ['id' => $id]);
    }

    /**
     * Test 11: Query Count Tracking
     * Verify getQueryCount() tracks executed queries
     */
    public function testQueryCount(): void {
        $initialCount = $this->db->getQueryCount();
        
        $this->db->query("SELECT 1");
        $this->db->query("SELECT 2");
        $this->db->query("SELECT 3");
        
        $finalCount = $this->db->getQueryCount();
        $this->assertEquals($initialCount + 3, $finalCount, 'Should track 3 additional queries');
    }

    /**
     * Test 12: SQL Injection Prevention
     * Verify that prepared statements prevent SQL injection
     */
    public function testSqlInjectionPrevention(): void {
        $maliciousInput = "1' OR '1'='1";
        
        // This should return 0 or 1 record, not all records
        $result = $this->db->fetch(
            "SELECT * FROM admin WHERE id = :id",
            ['id' => $maliciousInput]
        );
        
        // If SQL injection worked, this would return the first admin
        // With prepared statements, it should return null (no id matches the string)
        $this->assertNull($result, 'Prepared statements should prevent SQL injection');
    }
}
