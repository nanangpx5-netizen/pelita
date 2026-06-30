<?php
/**
 * PELITA - BukuTamu Model Unit Tests
 * @version 1.0.0
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/BukuTamu.php';

use PHPUnit\Framework\TestCase;

class BukuTamuTest extends TestCase {
    private BukuTamu $bukuTamu;
    private Database $db;

    protected function setUp(): void {
        $this->bukuTamu = new BukuTamu();
        $this->db = Database::getInstance();
    }

    /**
     * Test 1: Create Guest Entry
     * Verify that create() method inserts new guest record
     */
    public function testCreateGuest(): void {
        $data = [
            'nama' => 'Test User ' . uniqid(),
            'nohp' => '08123456789',
            'asal' => 'Test Institution',
            'keperluan' => 'Konsultasi Statistik',
            'jenis_kelamin' => 'Laki-laki',
            'umur' => 25,
            'pendidikan' => 'S1',
            'pekerjaan' => 'Mahasiswa',
            'email' => 'test@example.com',
            'alamat' => 'Test Address',
            'orang_ditemui' => 'Test Person',
            'rincian' => 'Test details'
        ];
        
        $id = $this->bukuTamu->create($data);
        $this->assertIsInt($id, 'create() should return integer ID');
        $this->assertGreaterThan(0, $id, 'create() should return positive ID');

        // Verify record exists
        $record = $this->bukuTamu->getById($id);
        $this->assertIsArray($record, 'Record should exist');
        $this->assertEquals($data['nama'], $record['nama'], 'Name should match');
        $this->assertEquals($data['asal'], $record['asal'], 'Institution should match');

        // Cleanup
        $this->db->delete('buku_tamu', "id = :id", ['id' => $id]);
    }

    /**
     * Test 2: Generate Queue Number
     * Verify that generateNomorAntrian() returns 3-digit number
     */
    public function testGenerateNomorAntrian(): void {
        $nomor = $this->bukuTamu->generateNomorAntrian();
        $this->assertMatchesRegularExpression('/^\d{3}$/', $nomor, 'Queue number should be 3 digits');
        $this->assertGreaterThanOrEqual(1, (int)$nomor, 'Queue number should be >= 1');
        $this->assertLessThanOrEqual(999, (int)$nomor, 'Queue number should be <= 999');
    }

    /**
     * Test 3: Get By ID
     * Verify that getById() returns correct record
     */
    public function testGetById(): void {
        // Insert test record
        $id = $this->db->insert('buku_tamu', [
            'tahun' => date('Y'),
            'bulan' => date('m'),
            'hari' => date('d'),
            'waktu' => date('H:i:s'),
            'nama' => 'Test GetById',
            'nohp' => '08123456789',
            'asal' => 'Test',
            'keperluan' => 'Test',
            'jenis_kelamin' => 'Laki-laki',
            'umur' => 25,
            'pendidikan' => '-',
            'pekerjaan' => '-',
            'nomor_antrian' => '001'
        ]);

        $record = $this->bukuTamu->getById($id);
        $this->assertIsArray($record, 'Should return array');
        $this->assertEquals($id, $record['id'], 'ID should match');
        $this->assertEquals('Test GetById', $record['nama'], 'Name should match');

        // Test non-existing ID
        $record = $this->bukuTamu->getById(999999);
        $this->assertNull($record, 'Should return null for non-existing ID');

        // Cleanup
        $this->db->delete('buku_tamu', "id = :id", ['id' => $id]);
    }

    /**
     * Test 4: Get Statistics
     * Verify that getStats() returns correct statistics
     */
    public function testGetStats(): void {
        $stats = $this->bukuTamu->getStats();
        
        $this->assertIsArray($stats, 'Should return array');
        $this->assertArrayHasKey('total', $stats, 'Should have total key');
        $this->assertArrayHasKey('hari_ini', $stats, 'Should have hari_ini key');
        $this->assertArrayHasKey('bulan_ini', $stats, 'Should have bulan_ini key');
        $this->assertArrayHasKey('tahun_ini', $stats, 'Should have tahun_ini key');
        
        $this->assertIsInt($stats['total'], 'Total should be integer');
        $this->assertGreaterThanOrEqual(0, $stats['total'], 'Total should be >= 0');
    }

    /**
     * Test 5: Get Filtered Data
     * Verify that getFiltered() returns paginated results
     */
    public function testGetFiltered(): void {
        // Insert test records
        $ids = [];
        for ($i = 1; $i <= 5; $i++) {
            $ids[] = $this->db->insert('buku_tamu', [
                'tahun' => 2026,
                'bulan' => '01',
                'hari' => str_pad($i, 2, '0', STR_PAD_LEFT),
                'waktu' => '10:00:00',
                'nama' => "Test Filtered $i",
                'nohp' => '08123456789',
                'asal' => 'Test Institution',
                'keperluan' => 'Konsultasi Statistik',
                'jenis_kelamin' => 'Laki-laki',
                'umur' => 25,
                'pendidikan' => '-',
                'pekerjaan' => '-',
                'nomor_antrian' => str_pad($i, 3, '0', STR_PAD_LEFT)
            ]);
        }

        // Test without filters
        $data = $this->bukuTamu->getFiltered(null, null, null, 1, 20);
        $this->assertIsArray($data, 'Should return array');
        $this->assertGreaterThanOrEqual(5, count($data), 'Should return at least 5 records');

        // Test with month filter
        $data = $this->bukuTamu->getFiltered('01', '2026', null, 1, 20);
        $this->assertIsArray($data, 'Should return array with month filter');
        $this->assertGreaterThanOrEqual(5, count($data), 'Should return at least 5 records');

        // Test with search filter
        $data = $this->bukuTamu->getFiltered(null, null, 'Test Filtered', 1, 20);
        $this->assertIsArray($data, 'Should return array with search filter');
        $this->assertGreaterThanOrEqual(5, count($data), 'Should return at least 5 records');

        // Cleanup
        foreach ($ids as $id) {
            $this->db->delete('buku_tamu', "id = :id", ['id' => $id]);
        }
    }

    /**
     * Test 6: Get Total Filtered Count
     * Verify that getTotalFiltered() returns correct count
     */
    public function testGetTotalFiltered(): void {
        // Insert test records
        $ids = [];
        for ($i = 1; $i <= 3; $i++) {
            $ids[] = $this->db->insert('buku_tamu', [
                'tahun' => 2026,
                'bulan' => '02',
                'hari' => str_pad($i, 2, '0', STR_PAD_LEFT),
                'waktu' => '10:00:00',
                'nama' => "Test Count $i",
                'nohp' => '08123456789',
                'asal' => 'Test Institution',
                'keperluan' => 'Konsultasi Statistik',
                'jenis_kelamin' => 'Laki-laki',
                'umur' => 25,
                'pendidikan' => '-',
                'pekerjaan' => '-',
                'nomor_antrian' => str_pad($i, 3, '0', STR_PAD_LEFT)
            ]);
        }

        $total = $this->bukuTamu->getTotalFiltered('02', '2026', null);
        $this->assertGreaterThanOrEqual(3, $total, 'Should count at least 3 records');

        // Cleanup
        foreach ($ids as $id) {
            $this->db->delete('buku_tamu', "id = :id", ['id' => $id]);
        }
    }

    /**
     * Test 7: Get Keperluan Statistics
     * Verify that getKeperluanStats() returns visit purpose statistics
     */
    public function testGetKeperluanStats(): void {
        $stats = $this->bukuTamu->getKeperluanStats(null, date('Y'));
        
        $this->assertIsArray($stats, 'Should return array');
        
        if (!empty($stats)) {
            $this->assertArrayHasKey('keperluan', $stats[0], 'Should have keperluan key');
            $this->assertArrayHasKey('jumlah', $stats[0], 'Should have jumlah key');
        }
    }

    /**
     * Test 8: Get Monthly Trend
     * Verify that getMonthlyTrend() returns monthly visit data
     */
    public function testGetMonthlyTrend(): void {
        $trend = $this->bukuTamu->getMonthlyTrend(date('Y'));
        
        $this->assertIsArray($trend, 'Should return array');
        
        if (!empty($trend)) {
            $this->assertArrayHasKey('bulan', $trend[0], 'Should have bulan key');
            $this->assertArrayHasKey('jumlah', $trend[0], 'Should have jumlah key');
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
            $ids[] = $this->db->insert('buku_tamu', [
                'tahun' => 2026,
                'bulan' => '03',
                'hari' => str_pad($i, 2, '0', STR_PAD_LEFT),
                'waktu' => '10:00:00',
                'nama' => "Test Export $i",
                'nohp' => '08123456789',
                'asal' => 'Test Institution',
                'keperluan' => 'Konsultasi Statistik',
                'jenis_kelamin' => 'Laki-laki',
                'umur' => 25,
                'pendidikan' => '-',
                'pekerjaan' => '-',
                'nomor_antrian' => str_pad($i, 3, '0', STR_PAD_LEFT)
            ]);
        }

        $data = $this->bukuTamu->getForExport('03', '2026');
        $this->assertIsArray($data, 'Should return array');
        $this->assertGreaterThanOrEqual(3, count($data), 'Should return at least 3 records');

        // Cleanup
        foreach ($ids as $id) {
            $this->db->delete('buku_tamu', "id = :id", ['id' => $id]);
        }
    }

    /**
     * Test 10: Delete Record
     * Verify that delete() removes record from database
     */
    public function testDelete(): void {
        // Insert test record
        $id = $this->db->insert('buku_tamu', [
            'tahun' => date('Y'),
            'bulan' => date('m'),
            'hari' => date('d'),
            'waktu' => date('H:i:s'),
            'nama' => 'Test Delete',
            'nohp' => '08123456789',
            'asal' => 'Test',
            'keperluan' => 'Test',
            'jenis_kelamin' => 'Laki-laki',
            'umur' => 25,
            'pendidikan' => '-',
            'pekerjaan' => '-',
            'nomor_antrian' => '001'
        ]);

        // Verify record exists
        $record = $this->bukuTamu->getById($id);
        $this->assertIsArray($record, 'Record should exist before delete');

        // Delete record
        $result = $this->bukuTamu->delete($id);
        $this->assertTrue($result, 'delete() should return true');

        // Verify record is deleted
        $record = $this->bukuTamu->getById($id);
        $this->assertNull($record, 'Record should be null after delete');
    }

    /**
     * Test 11: Auto-fill Date/Time
     * Verify that create() auto-fills date/time if not provided
     */
    public function testAutoFillDateTime(): void {
        $data = [
            'nama' => 'Test AutoFill',
            'nohp' => '08123456789',
            'asal' => 'Test',
            'keperluan' => 'Test',
            'jenis_kelamin' => 'Laki-laki',
            'umur' => 25,
            'pendidikan' => '-',
            'pekerjaan' => '-'
        ];
        
        $id = $this->bukuTamu->create($data);
        $record = $this->bukuTamu->getById($id);
        
        $this->assertEquals(date('Y'), $record['tahun'], 'Year should be auto-filled');
        $this->assertEquals(date('m'), $record['bulan'], 'Month should be auto-filled');
        $this->assertEquals(date('d'), $record['hari'], 'Day should be auto-filled');
        $this->assertNotEmpty($record['waktu'], 'Time should be auto-filled');

        // Cleanup
        $this->db->delete('buku_tamu', "id = :id", ['id' => $id]);
    }

    /**
     * Test 12: Queue Number Increment
     * Verify that queue numbers increment correctly
     */
    public function testQueueNumberIncrement(): void {
        $today = date('Y-m-d');
        
        // Get current count
        $countBefore = $this->db->count('buku_tamu', "DATE(created_at) = :today", ['today' => $today]);
        
        // Insert record
        $id = $this->db->insert('buku_tamu', [
            'tahun' => date('Y'),
            'bulan' => date('m'),
            'hari' => date('d'),
            'waktu' => date('H:i:s'),
            'nama' => 'Test Queue',
            'nohp' => '08123456789',
            'asal' => 'Test',
            'keperluan' => 'Test',
            'jenis_kelamin' => 'Laki-laki',
            'umur' => 25,
            'pendidikan' => '-',
            'pekerjaan' => '-',
            'nomor_antrian' => str_pad($countBefore + 1, 3, '0', STR_PAD_LEFT)
        ]);

        // Generate new queue number
        $newQueue = $this->bukuTamu->generateNomorAntrian();
        $expectedQueue = str_pad($countBefore + 2, 3, '0', STR_PAD_LEFT);
        
        $this->assertEquals($expectedQueue, $newQueue, 'Queue number should increment');

        // Cleanup
        $this->db->delete('buku_tamu', "id = :id", ['id' => $id]);
    }
}
