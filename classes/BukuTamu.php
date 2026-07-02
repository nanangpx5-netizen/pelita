<?php
/**
 * BukuTamu Model Class
 * @package PELITA
 * @version 1.0.0
 */

class BukuTamu {
    private Database $db;
    private string $table = 'buku_tamu';

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Create new buku tamu entry
     */
    public function create(array $data): int {
        // Auto-fill date/time if not provided
        $timestamp = isset($data['tanggal']) ? strtotime($data['tanggal']) : time();
        
        $data['tahun'] = date('Y', $timestamp);
        $data['bulan'] = date('m', $timestamp);
        $data['hari'] = date('d', $timestamp);
        
        // Remove 'tanggal' key as table uses separate tahun/bulan/hari columns
        unset($data['tanggal']);
        
        // Use provided time or current time
        if (!isset($data['waktu'])) {
            $data['waktu'] = date('H:i:s');
        }
        $data['nomor_antrian'] = $this->generateNomorAntrian();
        
        return $this->db->insert($this->table, $data);
    }

    /**
     * Generate nomor antrian (reset daily)
     */
    public function generateNomorAntrian(): string {
        $today = date('Y-m-d');
        $count = $this->db->count(
            $this->table,
            "tahun = :tahun AND bulan = :bulan AND hari = :hari",
            [
                'tahun' => date('Y'),
                'bulan' => date('m'),
                'hari' => date('d'),
            ]
        );
        return str_pad($count + 1, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Get filtered data with pagination
     */
    public function getFiltered(
        ?string $bulan = null, 
        ?string $tahun = null, 
        ?string $search = null, 
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
        
        if ($search) {
            $conditions[] = "(nama LIKE :search_nama OR email LIKE :search_email OR asal LIKE :search_asal)";
            $searchValue = "%{$search}%";
            $params['search_nama'] = $searchValue;
            $params['search_email'] = $searchValue;
            $params['search_asal'] = $searchValue;
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
        ?string $search = null
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
        
        if ($search) {
            $conditions[] = "(nama LIKE :search_nama OR email LIKE :search_email OR asal LIKE :search_asal)";
            $searchValue = "%{$search}%";
            $params['search_nama'] = $searchValue;
            $params['search_email'] = $searchValue;
            $params['search_asal'] = $searchValue;
        }

        $where = $conditions ? implode(" AND ", $conditions) : "1=1";
        
        return $this->db->count($this->table, $where, $params);
    }

    /**
     * Get single record by ID
     */
    public function getById(int $id): ?array {
        return $this->db->fetch(
            "SELECT * FROM {$this->table} WHERE id = :id", 
            ['id' => $id]
        );
    }

    /**
     * Get statistics
     */
    public function getStats(): array {
        $todayYear = date('Y');
        $todayMonth = date('m');
        $todayDay = date('d');

        return [
            'total' => $this->db->count($this->table),
            'hari_ini' => $this->db->count(
                $this->table,
                "tahun = :tahun AND bulan = :bulan AND hari = :hari",
                ['tahun' => $todayYear, 'bulan' => $todayMonth, 'hari' => $todayDay]
            ),
            'bulan_ini' => $this->db->count(
                $this->table,
                "tahun = :tahun AND bulan = :bulan",
                ['tahun' => $todayYear, 'bulan' => $todayMonth]
            ),
            'tahun_ini' => $this->db->count(
                $this->table,
                "tahun = :tahun",
                ['tahun' => $todayYear]
            ),
        ];
    }

    /**
     * Get keperluan statistics
     */
    public function getKeperluanStats(?string $bulan = null, ?string $tahun = null): array {
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
        
        $sql = "SELECT keperluan, COUNT(*) as jumlah 
                FROM {$this->table} {$where}
                GROUP BY keperluan 
                ORDER BY jumlah DESC";
        
        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Get monthly trend
     */
    public function getMonthlyTrend(int $year): array {
        $sql = "SELECT bulan, COUNT(*) as jumlah 
                FROM {$this->table} 
                WHERE tahun = :year 
                GROUP BY bulan 
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

    /**
     * Delete record
     */
    public function delete(int $id): bool {
        return $this->db->delete($this->table, "id = :id", ['id' => $id]) > 0;
    }
}
