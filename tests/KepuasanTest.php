<?php
/**
 * PELITA - Kepuasan Model Unit Tests
 * @version 1.0.0
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/Kepuasan.php';

use PHPUnit\Framework\TestCase;

class KepuasanTest extends TestCase {
    private Kepuasan $kepuasan;
    private Database $db;

    protected function setUp(): void {
        $this->kepuasan = new Kepuasan();
        $this->db = Database::getInstance();
    }

    /**
     * Test 1: Create Satisfaction Entry
     * Verify that create() method inserts new satisfaction record
     */
    public function testCreateSatisfaction(): void {
        $email = 'test' . uniqid() . '@example.com';
        $rating = 'Sangat Puas';
        $komentar = 'Excellent service!';
        
        $id = $this->kepuasan->create($email, $rating, $komentar);
        
        $this->assertIsInt($id, 'create() should return integer ID');
        $this->assertGreaterThan(0, $id, 'create() should return positive ID');

        // Verify record exists
        $data = $this->kepuasan->getFiltered(null, null, null, 1, 100);
        $found = false;
        foreach ($data as $row) {
            if ($row['id'] == $id) {
                $found = true;
                $this->assertEquals($email, $row['email'], 'Email should match');
                $this->assertEquals($rating, $row['rating'], 'Rating should match');
                $this->assertEquals($komentar, $row['komentar'], 'Comment should match');
                break;
            }
        }
        $this->assertTrue($found, 'Record should be found');

        // Cleanup
        $this->db->delete('kepuasan', "id = :id", ['id' => $id]);
    }

    /**
     * Test 2: Create with Empty Comment
     * Verify that create() works with null comment
     */
    public function testCreateWithNullComment(): void {
        $email = 'test' . uniqid() . '@example.com';
        $rating = 'Puas';
        
        $id = $this->kepuasan->create($email, $rating, null);
        
        $this->assertIsInt($id, 'create() should return integer ID');
        $this->assertGreaterThan(0, $id, 'create() should return positive ID');

        // Verify record exists
        $data = $this->kepuasan->getFiltered(null, null, null, 1, 100);
        $found = false;
        foreach ($data as $row) {
            if ($row['id'] == $id) {
                $found = true;
                $this->assertNull($row['komentar'], 'Comment should be null');
                break;
            }
        }
        $this->assertTrue($found, 'Record should be found');

        // Cleanup
        $this->db->delete('kepuasan', "id = :id", ['id' => $id]);
    }

    /**
     * Test 3: Create with Empty Email
     * Verify that create() works with empty email
     */
    public function testCreateWithEmptyEmail(): void {
        $email = '';
        $rating = 'Kurang Puas';
        $komentar = 'Need improvement';
        
        $id = $this->kepuasan->create($email, $rating, $komentar);
        
        $this->assertIsInt($id, 'create() should return integer ID');
        $this->assertGreaterThan(0, $id, 'create() should return positive ID');

        // Cleanup
        $this->db->delete('kepuasan', "id = :id", ['id' => $id]);
    }

    /**
     * Test 4: Get Filtered Data
     * Verify that getFiltered() returns paginated results
     */
    public function testGetFiltered(): void {
        // Insert test records
        $ids = [];
        $ratings = ['Sangat Puas', 'Puas', 'Kurang Puas'];
        
        for ($i = 0; $i < 6; $i++) {
            $ids[] = $this->db->insert('kepuasan', [
                'tahun' => 2026,
                'bulan' => '01',
                'hari' => str_pad($i + 1, 2, '0', STR_PAD_LEFT),
                'waktu' => '10:00:00',
                'email' => "test{$i}@example.com",
                'rating' => $ratings[$i % 3],
                'komentar' => "Test comment {$i}"
            ]);
        }

        // Test without filters
        $data = $this->kepuasan->getFiltered(null, null, null, 1, 20);
        $this->assertIsArray($data, 'Should return array');
        $this->assertGreaterThanOrEqual(6, count($data), 'Should return at least 6 records');

        // Test with month filter
        $data = $this->kepuasan->getFiltered('01', '2026', null, 1, 20);
        $this->assertIsArray($data, 'Should return array with month filter');
        $this->assertGreaterThanOrEqual(6, count($data), 'Should return at least 6 records');

        // Test with rating filter
        $data = $this->kepuasan->getFiltered(null, null, 'Sangat Puas', 1, 20);
        $this->assertIsArray($data, 'Should return array with rating filter');

        // Cleanup
        foreach ($ids as $id) {
            $this->db->delete('kepuasan', "id = :id", ['id' => $id]);
        }
    }

    /**
     * Test 5: Get Total Filtered Count
     * Verify that getTotalFiltered() returns correct count
     */
    public function testGetTotalFiltered(): void {
        // Insert test records
        $ids = [];
        for ($i = 0; $i < 3; $i++) {
            $ids[] = $this->db->insert('kepuasan', [
                'tahun' => 2026,
                'bulan' => '02',
                'hari' => str_pad($i + 1, 2, '0', STR_PAD_LEFT),
                'waktu' => '10:00:00',
                'email' => "test{$i}@example.com",
                'rating' => 'Sangat Puas',
                'komentar' => "Test comment {$i}"
            ]);
        }

        $total = $this->kepuasan->getTotalFiltered('02', '2026', null);
        $this->assertGreaterThanOrEqual(3, $total, 'Should count at least 3 records');

        $total = $this->kepuasan->getTotalFiltered('02', '2026', 'Sangat Puas');
        $this->assertGreaterThanOrEqual(3, $total, 'Should count at least 3 Sangat Puas records');

        // Cleanup
        foreach ($ids as $id) {
            $this->db->delete('kepuasan', "id = :id", ['id' => $id]);
        }
    }

    /**
     * Test 6: Get Statistics
     * Verify that getStats() returns correct satisfaction statistics
     */
    public function testGetStats(): void {
        // Insert test records with known ratings
        $ids = [];
        $ratings = ['Sangat Puas', 'Sangat Puas', 'Puas', 'Kurang Puas'];
        
        foreach ($ratings as $rating) {
            $ids[] = $this->db->insert('kepuasan', [
                'tahun' => 2026,
                'bulan' => '03',
                'hari' => '01',
                'waktu' => '10:00:00',
                'email' => 'test@example.com',
                'rating' => $rating,
                'komentar' => 'Test'
            ]);
        }

        $stats = $this->kepuasan->getStats('03', '2026');
        
        $this->assertIsArray($stats, 'Should return array');
        $this->assertArrayHasKey('Sangat Puas', $stats, 'Should have Sangat Puas key');
        $this->assertArrayHasKey('Puas', $stats, 'Should have Puas key');
        $this->assertArrayHasKey('Kurang Puas', $stats, 'Should have Kurang Puas key');
        $this->assertArrayHasKey('total', $stats, 'Should have total key');
        $this->assertArrayHasKey('persen_sangat_puas', $stats, 'Should have persen_sangat_puas key');
        $this->assertArrayHasKey('persen_puas', $stats, 'Should have persen_puas key');
        $this->assertArrayHasKey('persen_kurang_puas', $stats, 'Should have persen_kurang_puas key');

        $this->assertEquals(4, $stats['total'], 'Total should be 4');
        $this->assertEquals(2, $stats['Sangat Puas'], 'Sangat Puas should be 2');
        $this->assertEquals(1, $stats['Puas'], 'Puas should be 1');
        $this->assertEquals(1, $stats['Kurang Puas'], 'Kurang Puas should be 1');
        $this->assertEquals(50.0, $stats['persen_sangat_puas'], 'Sangat Puas % should be 50.0');
        $this->assertEquals(25.0, $stats['persen_puas'], 'Puas % should be 25.0');
        $this->assertEquals(25.0, $stats['persen_kurang_puas'], 'Kurang Puas % should be 25.0');

        // Cleanup
        foreach ($ids as $id) {
            $this->db->delete('kepuasan', "id = :id", ['id' => $id]);
        }
    }

    /**
     * Test 7: Get Statistics with No Data
     * Verify that getStats() returns zeros when no data exists
     */
    public function testGetStatsWithNoData(): void {
        $stats = $this->kepuasan->getStats('99', '2099');
        
        $this->assertIsArray($stats, 'Should return array');
        $this->assertEquals(0, $stats['Sangat Puas'], 'Sangat Puas should be 0');
        $this->assertEquals(0, $stats['Puas'], 'Puas should be 0');
        $this->assertEquals(0, $stats['Kurang Puas'], 'Kurang Puas should be 0');
        $this->assertEquals(0, $stats['total'], 'Total should be 0');
        $this->assertEquals(0, $stats['persen_sangat_puas'], 'Sangat Puas % should be 0');
        $this->assertEquals(0, $stats['persen_puas'], 'Puas % should be 0');
        $this->assertEquals(0, $stats['persen_kurang_puas'], 'Kurang Puas % should be 0');
    }

    /**
     * Test 8: Get Monthly Trend
     * Verify that getMonthlyTrend() returns monthly satisfaction data
     */
    public function testGetMonthlyTrend(): void {
        // Insert test records
        $ids = [];
        for ($i = 1; $i <= 3; $i++) {
            $ids[] = $this->db->insert('kepuasan', [
                'tahun' => 2026,
                'bulan' => str_pad($i, 2, '0', STR_PAD_LEFT),
                'hari' => '01',
                'waktu' => '10:00:00',
                'email' => 'test@example.com',
                'rating' => 'Sangat Puas',
                'komentar' => 'Test'
            ]);
        }

        $trend = $this->kepuasan->getMonthlyTrend(2026);
        
        $this->assertIsArray($trend, 'Should return array');
        
        if (!empty($trend)) {
            $this->assertArrayHasKey('bulan', $trend[0], 'Should have bulan key');
            $this->assertArrayHasKey('rating', $trend[0], 'Should have rating key');
            $this->assertArrayHasKey('jumlah', $trend[0], 'Should have jumlah key');
        }

        // Cleanup
        foreach ($ids as $id) {
            $this->db->delete('kepuasan', "id = :id", ['id' => $id]);
        }
    }

    /**
     * Test 9: Get Data for Export
     * Verify that getForExport() returns all data without pagination
     */
    public function testGetForExport(): void {
        // Insert test records
        $ids = [];
        for ($i = 1; $i <= 3; $i++) {
            $ids[] = $this->db->insert('kepuasan', [
                'tahun' => 2026,
                'bulan' => '04',
                'hari' => str_pad($i, 2, '0', STR_PAD_LEFT),
                'waktu' => '10:00:00',
                'email' => "test{$i}@example.com",
                'rating' => 'Sangat Puas',
                'komentar' => "Test comment {$i}"
            ]);
        }

        $data = $this->kepuasan->getForExport('04', '2026');
        $this->assertIsArray($data, 'Should return array');
        $this->assertGreaterThanOrEqual(3, count($data), 'Should return at least 3 records');

        // Cleanup
        foreach ($ids as $id) {
            $this->db->delete('kepuasan', "id = :id", ['id' => $id]);
        }
    }

    /**
     * Test 10: Auto-fill Date/Time
     * Verify that create() auto-fills date/time
     */
    public function testAutoFillDateTime(): void {
        $id = $this->kepuasan->create('test@example.com', 'Sangat Puas', 'Test');
        
        $data = $this->kepuasan->getFiltered(null, null, null, 1, 100);
        $record = null;
        foreach ($data as $row) {
            if ($row['id'] == $id) {
                $record = $row;
                break;
            }
        }
        
        $this->assertNotNull($record, 'Record should exist');
        $this->assertEquals(date('Y'), $record['tahun'], 'Year should be auto-filled');
        $this->assertEquals(date('m'), $record['bulan'], 'Month should be auto-filled');
        $this->assertEquals(date('d'), $record['hari'], 'Day should be auto-filled');
        $this->assertNotEmpty($record['waktu'], 'Time should be auto-filled');

        // Cleanup
        $this->db->delete('kepuasan', "id = :id", ['id' => $id]);
    }

    /**
     * Test 11: Valid Rating Values
     * Verify that only valid rating values are accepted
     */
    public function testValidRatingValues(): void {
        $validRatings = ['Sangat Puas', 'Puas', 'Kurang Puas'];
        
        foreach ($validRatings as $rating) {
            $id = $this->kepuasan->create('test@example.com', $rating, 'Test');
            $this->assertIsInt($id, "Should accept rating: {$rating}");
            $this->db->delete('kepuasan', "id = :id", ['id' => $id]);
        }
    }

    /**
     * Test 12: Pagination
     * Verify that pagination works correctly
     */
    public function testPagination(): void {
        // Insert test records
        $ids = [];
        for ($i = 1; $i <= 25; $i++) {
            $ids[] = $this->db->insert('kepuasan', [
                'tahun' => 2026,
                'bulan' => '05',
                'hari' => '01',
                'waktu' => '10:00:00',
                'email' => "test{$i}@example.com",
                'rating' => 'Sangat Puas',
                'komentar' => "Test comment {$i}"
            ]);
        }

        // Page 1
        $page1 = $this->kepuasan->getFiltered('05', '2026', null, 1, 20);
        $this->assertCount(20, $page1, 'Page 1 should have 20 records');

        // Page 2
        $page2 = $this->kepuasan->getFiltered('05', '2026', null, 2, 20);
        $this->assertGreaterThanOrEqual(5, count($page2), 'Page 2 should have at least 5 records');

        // Cleanup
        foreach ($ids as $id) {
            $this->db->delete('kepuasan', "id = :id", ['id' => $id]);
        }
    }
}
