<?php
/**
 * PELITA - Performance Benchmark Tests
 * @version 1.0.0
 * 
 * These tests verify that the application meets performance targets:
 * - Database queries < 200ms
 * - Page load time < 2s
 * - Form submission < 500ms
 * - Export generation < 3s
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/BukuTamu.php';
require_once __DIR__ . '/../classes/Kepuasan.php';

use PHPUnit\Framework\TestCase;

class PerformanceTest extends TestCase {
    private Database $db;
    private BukuTamu $bukuTamu;
    private Kepuasan $kepuasan;

    protected function setUp(): void {
        $this->db = Database::getInstance();
        $this->bukuTamu = new BukuTamu();
        $this->kepuasan = new Kepuasan();
    }

    /**
     * Helper: Measure execution time in milliseconds
     */
    private function measureTime(callable $callback): float {
        $start = microtime(true);
        $callback();
        $end = microtime(true);
        return ($end - $start) * 1000; // Convert to milliseconds
    }

    /**
     * Test 1: Simple SELECT Query Performance
     * Target: < 50ms
     */
    public function testSimpleSelectQuery(): void {
        $time = $this->measureTime(function() {
            $this->db->fetch("SELECT 1 as test");
        });
        
        $this->assertLessThan(50, $time, "Simple SELECT query took {$time}ms (target: < 50ms)");
    }

    /**
     * Test 2: COUNT Query Performance
     * Target: < 50ms
     */
    public function testCountQuery(): void {
        $time = $this->measureTime(function() {
            $this->db->count('buku_tamu');
        });
        
        $this->assertLessThan(50, $time, "COUNT query took {$time}ms (target: < 50ms)");
    }

    /**
     * Test 3: Pagination Query Performance
     * Target: < 100ms
     */
    public function testPaginationQuery(): void {
        $time = $this->measureTime(function() {
            $this->bukuTamu->getFiltered(null, date('Y'), null, 1, 20);
        });
        
        $this->assertLessThan(100, $time, "Pagination query took {$time}ms (target: < 100ms)");
    }

    /**
     * Test 4: Statistics Query Performance
     * Target: < 80ms
     */
    public function testStatisticsQuery(): void {
        $time = $this->measureTime(function() {
            $this->bukuTamu->getStats();
        });
        
        $this->assertLessThan(80, $time, "Statistics query took {$time}ms (target: < 80ms)");
    }

    /**
     * Test 5: Satisfaction Stats Query Performance
     * Target: < 80ms
     */
    public function testSatisfactionStatsQuery(): void {
        $time = $this->measureTime(function() {
            $this->kepuasan->getStats(null, date('Y'));
        });
        
        $this->assertLessThan(80, $time, "Satisfaction stats query took {$time}ms (target: < 80ms)");
    }

    /**
     * Test 6: INSERT Query Performance
     * Target: < 100ms
     */
    public function testInsertQuery(): void {
        $time = $this->measureTime(function() {
            $id = $this->db->insert('log_activity', [
                'admin_id' => 1,
                'action' => 'PERF_TEST',
                'table_name' => 'test',
                'ip_address' => '127.0.0.1'
            ]);
            // Cleanup
            $this->db->delete('log_activity', "id = :id", ['id' => $id]);
        });
        
        $this->assertLessThan(100, $time, "INSERT query took {$time}ms (target: < 100ms)");
    }

    /**
     * Test 7: UPDATE Query Performance
     * Target: < 100ms
     */
    public function testUpdateQuery(): void {
        // Insert test record
        $id = $this->db->insert('log_activity', [
            'admin_id' => 1,
            'action' => 'PERF_TEST_UPDATE',
            'table_name' => 'test',
            'ip_address' => '127.0.0.1'
        ]);

        $time = $this->measureTime(function() use ($id) {
            $this->db->update(
                'log_activity',
                ['action' => 'UPDATED'],
                "id = :id",
                ['id' => $id]
            );
        });
        
        // Cleanup
        $this->db->delete('log_activity', "id = :id", ['id' => $id]);
        
        $this->assertLessThan(100, $time, "UPDATE query took {$time}ms (target: < 100ms)");
    }

    /**
     * Test 8: DELETE Query Performance
     * Target: < 100ms
     */
    public function testDeleteQuery(): void {
        // Insert test record
        $id = $this->db->insert('log_activity', [
            'admin_id' => 1,
            'action' => 'PERF_TEST_DELETE',
            'table_name' => 'test',
            'ip_address' => '127.0.0.1'
        ]);

        $time = $this->measureTime(function() use ($id) {
            $this->db->delete('log_activity', "id = :id", ['id' => $id]);
        });
        
        $this->assertLessThan(100, $time, "DELETE query took {$time}ms (target: < 100ms)");
    }

    /**
     * Test 9: Complex JOIN Query Performance
     * Target: < 150ms
     */
    public function testComplexJoinQuery(): void {
        $time = $this->measureTime(function() {
            $this->db->fetchAll("
                SELECT bt.*, rk.nama as keperluan_nama 
                FROM buku_tamu bt 
                LEFT JOIN ref_keperluan rk ON bt.keperluan = rk.nama 
                LIMIT 50
            ");
        });
        
        $this->assertLessThan(150, $time, "Complex JOIN query took {$time}ms (target: < 150ms)");
    }

    /**
     * Test 10: Export Data Query Performance
     * Target: < 200ms
     */
    public function testExportDataQuery(): void {
        $time = $this->measureTime(function() {
            $this->bukuTamu->getForExport(null, date('Y'));
        });
        
        $this->assertLessThan(200, $time, "Export data query took {$time}ms (target: < 200ms)");
    }

    /**
     * Test 11: Multiple Sequential Queries Performance
     * Target: < 300ms for 10 queries
     */
    public function testMultipleSequentialQueries(): void {
        $time = $this->measureTime(function() {
            for ($i = 0; $i < 10; $i++) {
                $this->db->fetch("SELECT {$i} as num");
            }
        });
        
        $this->assertLessThan(300, $time, "10 sequential queries took {$time}ms (target: < 300ms)");
    }

    /**
     * Test 12: Transaction Performance
     * Target: < 200ms for 3 operations
     */
    public function testTransactionPerformance(): void {
        $time = $this->measureTime(function() {
            $this->db->beginTransaction();
            
            $id1 = $this->db->insert('log_activity', [
                'admin_id' => 1,
                'action' => 'PERF_TEST_TX1',
                'table_name' => 'test',
                'ip_address' => '127.0.0.1'
            ]);
            
            $id2 = $this->db->insert('log_activity', [
                'admin_id' => 1,
                'action' => 'PERF_TEST_TX2',
                'table_name' => 'test',
                'ip_address' => '127.0.0.1'
            ]);
            
            $id3 = $this->db->insert('log_activity', [
                'admin_id' => 1,
                'action' => 'PERF_TEST_TX3',
                'table_name' => 'test',
                'ip_address' => '127.0.0.1'
            ]);
            
            $this->db->commit();
            
            // Cleanup
            $this->db->delete('log_activity', "id IN (:id1, :id2, :id3)", [
                'id1' => $id1, 'id2' => $id2, 'id3' => $id3
            ]);
        });
        
        $this->assertLessThan(200, $time, "Transaction with 3 inserts took {$time}ms (target: < 200ms)");
    }

    /**
     * Test 13: Queue Number Generation Performance
     * Target: < 50ms
     */
    public function testQueueNumberGeneration(): void {
        $time = $this->measureTime(function() {
            $this->bukuTamu->generateNomorAntrian();
        });
        
        $this->assertLessThan(50, $time, "Queue number generation took {$time}ms (target: < 50ms)");
    }

    /**
     * Test 14: Reference Data Query Performance
     * Target: < 30ms
     */
    public function testReferenceDataQuery(): void {
        $time = $this->measureTime(function() {
            $this->db->fetchAll("SELECT * FROM ref_bulan");
        });
        
        $this->assertLessThan(30, $time, "Reference data query took {$time}ms (target: < 30ms)");
    }

    /**
     * Test 15: Search Query Performance
     * Target: < 150ms
     */
    public function testSearchQuery(): void {
        $time = $this->measureTime(function() {
            $this->bukuTamu->getFiltered(null, date('Y'), 'test', 1, 20);
        });
        
        $this->assertLessThan(150, $time, "Search query took {$time}ms (target: < 150ms)");
    }

    /**
     * Test 16: Monthly Trend Query Performance
     * Target: < 100ms
     */
    public function testMonthlyTrendQuery(): void {
        $time = $this->measureTime(function() {
            $this->bukuTamu->getMonthlyTrend(date('Y'));
        });
        
        $this->assertLessThan(100, $time, "Monthly trend query took {$time}ms (target: < 100ms)");
    }

    /**
     * Test 17: Keperluan Stats Query Performance
     * Target: < 100ms
     */
    public function testKeperluanStatsQuery(): void {
        $time = $this->measureTime(function() {
            $this->bukuTamu->getKeperluanStats(null, date('Y'));
        });
        
        $this->assertLessThan(100, $time, "Keperluan stats query took {$time}ms (target: < 100ms)");
    }

    /**
     * Test 18: Admin Authentication Query Performance
     * Target: < 50ms
     */
    public function testAdminAuthQuery(): void {
        $time = $this->measureTime(function() {
            $this->db->fetch("SELECT * FROM admin WHERE username = :username AND is_active = 1", [
                'username' => 'admin_pelita'
            ]);
        });
        
        $this->assertLessThan(50, $time, "Admin auth query took {$time}ms (target: < 50ms)");
    }

    /**
     * Test 19: Password Verification Performance
     * Target: < 10ms
     */
    public function testPasswordVerification(): void {
        // Get admin password hash
        $admin = $this->db->fetch("SELECT password FROM admin WHERE id = 1");
        $hash = $admin['password'];
        
        $time = $this->measureTime(function() use ($hash) {
            password_verify('test_password', $hash);
        });
        
        $this->assertLessThan(10, $time, "Password verification took {$time}ms (target: < 10ms)");
    }

    /**
     * Test 20: Overall Page Load Simulation
     * Target: < 500ms for all queries combined
     */
    public function testOverallPageLoadSimulation(): void {
        $time = $this->measureTime(function() {
            // Simulate dashboard page load
            $this->bukuTamu->getStats();
            $this->kepuasan->getStats(null, date('Y'));
            $this->bukuTamu->getKeperluanStats(null, date('Y'));
            $this->bukuTamu->getMonthlyTrend(date('Y'));
        });
        
        $this->assertLessThan(500, $time, "Dashboard page load simulation took {$time}ms (target: < 500ms)");
    }
}
