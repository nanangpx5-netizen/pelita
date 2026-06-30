<?php
/**
 * Kepuasan Model Class
 * @package PELITA
 * @version 1.0.0
 */

class Kepuasan {
    private Database $db;
    private string $table = 'kepuasan';

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Create new kepuasan entry
     */
    public function create(string $email, string $rating, ?string $komentar = null): int {
        return $this->db->insert($this->table, [
            'tahun' => date('Y'),
            'bulan' => date('m'),
            'hari' => date('d'),
            'waktu' => date('H:i:s'),
            'email' => $email,
            'rating' => $rating,
            'komentar' => $komentar
        ]);
    }

    /**
     * Get filtered data with pagination
     */
    public function getFiltered(
        ?string $bulan = null, 
        ?string $tahun = null, 
        ?string $rating = null,
        int $page = 1, 
        int $limit = ITEMS_PER_PAGE
    ): array {
        $offset = ($page - 1) * $limit;
        $params = [];
        $conditions = [];

        if ($bulan) {
            $conditions[] = "bulan = :bulan";
            $params['bulan'] = str_pad($bulan, 2, '0', STR_PAD_LEFT);
        }
        
        if ($tahun) {
            $conditions[] = "tahun = :tahun";
            $params['tahun'] = $tahun;
        }

        if ($rating) {
            $conditions[] = "rating = :rating";
            $params['rating'] = $rating;
        }

        $where = $conditions ? implode(" AND ", $conditions) : "1=1";
        
        $sql = "SELECT * FROM {$this->table} WHERE {$where} ORDER BY id DESC LIMIT {$limit} OFFSET {$offset}";
        
        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Get total count with filters
     */
    public function getTotalFiltered(
        ?string $bulan = null, 
        ?string $tahun = null, 
        ?string $rating = null
    ): int {
        $params = [];
        $conditions = [];

        if ($bulan) {
            $conditions[] = "bulan = :bulan";
            $params['bulan'] = str_pad($bulan, 2, '0', STR_PAD_LEFT);
        }
        
        if ($tahun) {
            $conditions[] = "tahun = :tahun";
            $params['tahun'] = $tahun;
        }

        if ($rating) {
            $conditions[] = "rating = :rating";
            $params['rating'] = $rating;
        }

        $where = $conditions ? implode(" AND ", $conditions) : "1=1";
        
        return $this->db->count($this->table, $where, $params);
    }

    /**
     * Get rating statistics
     */
    public function getStats(?string $bulan = null, ?string $tahun = null): array {
        $params = [];
        $conditions = [];

        if ($bulan) {
            $conditions[] = "bulan = :bulan";
            $params['bulan'] = str_pad($bulan, 2, '0', STR_PAD_LEFT);
        }
        
        if ($tahun) {
            $conditions[] = "tahun = :tahun";
            $params['tahun'] = $tahun;
        }

        $where = $conditions ? "WHERE " . implode(" AND ", $conditions) : "";
        
        $sql = "SELECT rating, COUNT(*) as jumlah 
                FROM {$this->table} {$where}
                GROUP BY rating";
        
        $results = $this->db->fetchAll($sql, $params);
        
        // Format as associative array
        $stats = [
            'Sangat Puas' => 0,
            'Puas' => 0,
            'Kurang Puas' => 0,
            'total' => 0
        ];
        
        foreach ($results as $row) {
            $stats[$row['rating']] = (int) $row['jumlah'];
            $stats['total'] += (int) $row['jumlah'];
        }
        
        // Calculate percentages
        if ($stats['total'] > 0) {
            $stats['persen_sangat_puas'] = round(($stats['Sangat Puas'] / $stats['total']) * 100, 1);
            $stats['persen_puas'] = round(($stats['Puas'] / $stats['total']) * 100, 1);
            $stats['persen_kurang_puas'] = round(($stats['Kurang Puas'] / $stats['total']) * 100, 1);
        } else {
            $stats['persen_sangat_puas'] = 0;
            $stats['persen_puas'] = 0;
            $stats['persen_kurang_puas'] = 0;
        }
        
        return $stats;
    }

    /**
     * Get monthly trend
     */
    public function getMonthlyTrend(int $year): array {
        $sql = "SELECT bulan, rating, COUNT(*) as jumlah 
                FROM {$this->table} 
                WHERE tahun = :year 
                GROUP BY bulan, rating 
                ORDER BY bulan ASC";
        
        return $this->db->fetchAll($sql, ['year' => $year]);
    }

    /**
     * Get data for export
     */
    public function getForExport(?string $bulan = null, ?string $tahun = null): array {
        $params = [];
        $conditions = [];

        if ($bulan) {
            $conditions[] = "bulan = :bulan";
            $params['bulan'] = str_pad($bulan, 2, '0', STR_PAD_LEFT);
        }
        
        if ($tahun) {
            $conditions[] = "tahun = :tahun";
            $params['tahun'] = $tahun;
        }

        $where = $conditions ? "WHERE " . implode(" AND ", $conditions) : "";
        
        $sql = "SELECT * FROM {$this->table} {$where} ORDER BY id ASC";
        
        return $this->db->fetchAll($sql, $params);
    }
}
